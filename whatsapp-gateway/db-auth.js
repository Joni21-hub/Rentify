/**
 * WhatsApp Auth State Adapter for Rentify Gateway
 * Persists Baileys session keys into MySQL to survive container restarts & deployments.
 * Falls back to local filesystem if MySQL is unavailable.
 */

const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
const { initAuthCreds, BufferJSON, proto } = require('@whiskeysockets/baileys');

// Singleton connection pool across the entire Node.js runtime
let cachedPool = null;

function getDbPool() {
    if (cachedPool) return cachedPool;

    const dbHost = process.env.DB_HOST;
    const dbUser = process.env.DB_USERNAME || process.env.DB_USER;
    const dbPassword = process.env.DB_PASSWORD || '';
    const dbName = process.env.DB_DATABASE || process.env.DB_NAME;
    const dbPort = parseInt(process.env.DB_PORT || '3306', 10);

    // If MySQL config is present, create a lightweight connection pool
    if (dbHost && dbUser && dbName) {
        try {
            cachedPool = mysql.createPool({
                host: dbHost,
                port: dbPort,
                user: dbUser,
                password: dbPassword,
                database: dbName,
                waitForConnections: true,
                connectionLimit: 2, // Minimal footprint: max 2 connections for Node.js gateway
                maxIdle: 2,
                idleTimeout: 30000,
                queueLimit: 0,
                enableKeepAlive: true,
                keepAliveInitialDelay: 10000,
                ssl: process.env.DB_SSL === 'true' ? { rejectUnauthorized: false } : undefined
            });
            return cachedPool;
        } catch (err) {
            console.error('[DB-AUTH] Failed to initialize MySQL pool:', err.message);
            cachedPool = null;
            return null;
        }
    }
    return null;
}

async function createAuthState(options = {}) {
    const pool = getDbPool();

    // If MySQL config is present, try database persistence
    if (pool) {
        try {
            // Verify connection & ensure table exists
            await pool.query(`
                CREATE TABLE IF NOT EXISTS whatsapp_sessions (
                    \`key\` VARCHAR(191) PRIMARY KEY,
                    \`value\` LONGTEXT NOT NULL,
                    \`created_at\` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    \`updated_at\` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            `);

            console.log('[DB-AUTH] Connected to MySQL whatsapp_sessions table successfully.');

            // Read creds
            const [rows] = await pool.query('SELECT `value` FROM whatsapp_sessions WHERE `key` = ?', ['creds']);
            let creds;
            if (rows.length > 0 && rows[0].value) {
                try {
                    creds = JSON.parse(rows[0].value, BufferJSON.reviver);
                } catch (e) {
                    console.error('[DB-AUTH] Failed to parse existing creds, initializing fresh:', e.message);
                    creds = initAuthCreds();
                }
            } else {
                creds = initAuthCreds();
            }

            return {
                state: {
                    creds,
                    keys: {
                        get: async (type, ids) => {
                            const data = {};
                            if (!ids || ids.length === 0) return data;

                            const keyNames = ids.map(id => `${type}-${id}`);
                            try {
                                const [rows] = await pool.query(
                                    'SELECT `key`, `value` FROM whatsapp_sessions WHERE `key` IN (?)',
                                    [keyNames]
                                );
                                for (const row of rows) {
                                    const rawId = row.key.substring(type.length + 1);
                                    if (row.value) {
                                        try {
                                            let val = JSON.parse(row.value, BufferJSON.reviver);
                                            if (type === 'app-state-sync-key' && val) {
                                                val = proto.Message.AppStateSyncKeyData.fromObject(val);
                                            }
                                            data[rawId] = val;
                                        } catch (pe) {
                                            console.error(`[DB-AUTH] Parse error for ${row.key}:`, pe.message);
                                        }
                                    }
                                }
                            } catch (err) {
                                console.error(`[DB-AUTH] Error fetching keys (${type}):`, err.message);
                            }
                            return data;
                        },
                        set: async (data) => {
                            const tasks = [];
                            for (const category of Object.keys(data)) {
                                for (const id of Object.keys(data[category])) {
                                    const value = data[category][id];
                                    const keyName = `${category}-${id}`;
                                    if (value) {
                                        const serialized = JSON.stringify(value, BufferJSON.replacer);
                                        tasks.push(
                                            pool.query(
                                                'INSERT INTO whatsapp_sessions (`key`, `value`, `created_at`, `updated_at`) VALUES (?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()',
                                                [keyName, serialized]
                                            ).catch(err => console.error(`[DB-AUTH] Error saving key ${keyName}:`, err.message))
                                        );
                                    } else {
                                        tasks.push(
                                            pool.query('DELETE FROM whatsapp_sessions WHERE `key` = ?', [keyName])
                                                .catch(err => console.error(`[DB-AUTH] Error deleting key ${keyName}:`, err.message))
                                        );
                                    }
                                }
                            }
                            await Promise.all(tasks);
                        }
                    }
                },
                saveCreds: async () => {
                    try {
                        const serialized = JSON.stringify(creds, BufferJSON.replacer);
                        await pool.query(
                            'INSERT INTO whatsapp_sessions (`key`, `value`, `created_at`, `updated_at`) VALUES (\'creds\', ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()',
                            [serialized]
                        );
                    } catch (err) {
                        console.error('[DB-AUTH] Error saving creds:', err.message);
                    }
                },
                clearChatSessions: async () => {
                    try {
                        await pool.query("DELETE FROM whatsapp_sessions WHERE `key` LIKE 'session-%' OR `key` LIKE 'sender-key-%'");
                        console.log('[DB-AUTH] Cleared all broken session and sender-key ratchets from database (login creds intact).');
                    } catch (err) {
                        console.error('[DB-AUTH] Error clearing chat sessions:', err.message);
                    }
                },
                clearAuth: async () => {
                    try {
                        await pool.query('DELETE FROM whatsapp_sessions');
                        console.log('[DB-AUTH] Cleared all auth credentials from database.');
                    } catch (err) {
                        console.error('[DB-AUTH] Error clearing auth:', err.message);
                    }
                }
            };
        } catch (dbErr) {
            console.warn('[DB-AUTH] MySQL connection failed, falling back to filesystem storage:', dbErr.message);
        }
    }

    // Fallback: Local filesystem storage
    const sessionDir = path.resolve(options.sessionDir || path.join(__dirname, '../storage/whatsapp-session'));
    if (!fs.existsSync(sessionDir)) {
        fs.mkdirSync(sessionDir, { recursive: true });
    }

    console.log('[FILE-AUTH] Using filesystem storage at:', sessionDir);

    const { useMultiFileAuthState } = require('@whiskeysockets/baileys');
    const authState = await useMultiFileAuthState(sessionDir);

    return {
        ...authState,
        clearAuth: async () => {
            try {
                if (fs.existsSync(sessionDir)) {
                    fs.rmSync(sessionDir, { recursive: true, force: true });
                    fs.mkdirSync(sessionDir, { recursive: true });
                }
                console.log('[FILE-AUTH] Cleared filesystem session files.');
            } catch (err) {
                console.error('[FILE-AUTH] Error clearing auth files:', err.message);
            }
        }
    };
}

module.exports = { createAuthState };
