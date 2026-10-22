/**
 * Rentify Self-Hosted WhatsApp Gateway
 * Built with @whiskeysockets/baileys
 * Provides lightweight REST API for Laravel & handles incoming message webhooks
 */

const express = require('express');
const qrcode = require('qrcode');
const pino = require('pino');
const path = require('path');
const dotenv = require('dotenv');

// Load .env from project root or current folder
dotenv.config({ path: path.resolve(__dirname, '../.env') });
dotenv.config(); // fallback to local

const {
    default: makeWASocket,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore
} = require('@whiskeysockets/baileys');

const { createAuthState } = require('./db-auth');

let NodeCache;
try {
    NodeCache = require('@cacheable/node-cache').default || require('@cacheable/node-cache').NodeCache || require('@cacheable/node-cache');
} catch (e) {
    NodeCache = class {
        constructor() { this.map = new Map(); }
        get(k) { return this.map.get(k); }
        set(k, v) { this.map.set(k, v); }
        del(k) { this.map.delete(k); }
        flushAll() { this.map.clear(); }
    };
}
const msgRetryCounterCache = new NodeCache({ stdTTL: 300 });
const msgStore = new Map(); // Cache recent messages for Baileys getMessage / decryption retry

const PORT = parseInt(process.env.WHATSAPP_GATEWAY_PORT || '3000', 10);
const GATEWAY_SECRET = process.env.WHATSAPP_GATEWAY_SECRET || 'rentify-wa-secret-2026';
const WEBHOOK_URL = process.env.WHATSAPP_WEBHOOK_URL || 'http://127.0.0.1/webhook/whatsapp';

let sock = null;
let currentQrDataUrl = null;
let currentQrRaw = null;
let connectionState = 'disconnected'; // 'disconnected' | 'connecting' | 'qr_ready' | 'connected'
let connectedUser = null;
let authAdapter = null;
let reconnectTimer = null;

// Debounce buffer for rapid sequential incoming messages from the same sender
const incomingDebounceBuffers = new Map(); // senderKey -> { messages: [], name: '', timer: Timeout, targetJid: string }
const DEBOUNCE_DELAY_MS = 2500;

// Helper: Normalize Indonesian phone numbers or pass through valid JIDs
function formatJid(target) {
    if (!target) return null;
    const str = String(target).trim();
    if (str.endsWith('@s.whatsapp.net') || str.endsWith('@lid') || str.endsWith('@g.us')) {
        return str;
    }
    // Check if target matches Bos Joni's known LID
    if (str.includes('249070069219550') || str.includes('44925643173935')) {
        return '6283183494835@s.whatsapp.net';
    }
    let clean = str.replace(/[^0-9]/g, '');
    if (clean.length >= 14 && !clean.startsWith('62')) {
        return `${clean}@lid`;
    }
    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    } else if (clean.startsWith('+62')) {
        clean = clean.slice(1);
    } else if (!clean.startsWith('62')) {
        clean = '62' + clean;
    }
    return `${clean}@s.whatsapp.net`;
}

// Forward incoming message to Laravel Webhook
async function forwardToWebhook(senderPhone, senderName, textMessage) {
    try {
        console.log(`[GATEWAY -> WEBHOOK] Forwarding message from ${senderPhone} (${senderName}): "${textMessage.slice(0, 50)}..."`);
        
        const payload = {
            sender: senderPhone,
            name: senderName || 'Pengguna WhatsApp',
            message: textMessage,
            gateway_secret: GATEWAY_SECRET
        };

        const res = await fetch(WEBHOOK_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Gateway-Secret': GATEWAY_SECRET
            },
            body: JSON.stringify(payload)
        });

        if (!res.ok) {
            const errText = await res.text();
            console.warn(`[WEBHOOK] Warning: Webhook returned HTTP ${res.status}: ${errText.slice(0, 100)}`);
        } else {
            console.log(`[WEBHOOK] Webhook response HTTP ${res.status} OK`);
        }
    } catch (err) {
        console.error('[WEBHOOK] Failed to post to Laravel webhook:', err.message);
    }
}

// Start Baileys WhatsApp Connection
async function startWhatsApp() {
    if (reconnectTimer) {
        clearTimeout(reconnectTimer);
        reconnectTimer = null;
    }

    if (sock) {
        try {
            sock.ev.removeAllListeners();
            sock.end(undefined);
        } catch (e) {
            // ignore
        }
        sock = null;
    }

    connectionState = 'connecting';
    console.log('[WHATSAPP] Initializing WhatsApp multi-device connection...');

    if (!authAdapter) {
        authAdapter = await createAuthState();
    }
    const { version, isLatest } = await fetchLatestBaileysVersion();
    console.log(`[WHATSAPP] Using Baileys version ${version.join('.')} (latest: ${isLatest})`);

    const logger = pino({ level: 'warn' });
    const keyStore = makeCacheableSignalKeyStore(authAdapter.state.keys, logger);

    sock = makeWASocket({
        version,
        logger,
        printQRInTerminal: false,
        auth: {
            creds: authAdapter.state.creds,
            keys: keyStore,
        },
        msgRetryCounterCache,
        getMessage: async (key) => {
            if (key?.id && msgStore.has(key.id)) {
                return msgStore.get(key.id);
            }
            return undefined;
        },
        browser: ['Rentify Platform', 'Chrome', '124.0.0.0'],
        syncFullHistory: false,
        markOnlineOnConnect: true,
        generateHighQualityLinkPreview: false,
    });

    // Handle connection events
    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            currentQrRaw = qr;
            try {
                currentQrDataUrl = await qrcode.toDataURL(qr, { margin: 2, scale: 7 });
                connectionState = 'qr_ready';
                console.log('[WHATSAPP] New QR Code generated, ready to scan via admin panel.');
                
                // Also display terminal QR for CLI convenience
                qrcode.toString(qr, { type: 'terminal', small: true }, (err, str) => {
                    if (!err) console.log(str);
                });
            } catch (err) {
                console.error('[WHATSAPP] Failed to render QR code:', err.message);
            }
        }

        if (connection === 'close') {
            currentQrDataUrl = null;
            currentQrRaw = null;
            connectedUser = null;
            connectionState = 'disconnected';

            const statusCode = lastDisconnect?.error?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
            console.log(`[WHATSAPP] Connection closed (code: ${statusCode}). Reconnect? ${shouldReconnect}`);

            if (statusCode === DisconnectReason.loggedOut) {
                console.log('[WHATSAPP] Session logged out by user. Clearing auth...');
                if (authAdapter) {
                    await authAdapter.clearAuth();
                    authAdapter = null;
                }
                reconnectTimer = setTimeout(startWhatsApp, 3000);
            } else if (shouldReconnect) {
                console.log('[WHATSAPP] Reconnecting in 5 seconds...');
                reconnectTimer = setTimeout(startWhatsApp, 5000);
            }
        } else if (connection === 'open') {
            currentQrDataUrl = null;
            currentQrRaw = null;
            connectionState = 'connected';

            const rawId = sock.user?.id || '';
            const phone = rawId.split(':')[0] || rawId.split('@')[0];
            connectedUser = {
                id: sock.user?.id,
                phone: phone,
                name: sock.user?.name || 'Rentify Official'
            };

            console.log(`[WHATSAPP] Connection opened successfully! Logged in as: ${phone} (${connectedUser.name})`);
        }
    });

    // Save auth credentials whenever updated
    sock.ev.on('creds.update', authAdapter.saveCreds);

    // Handle incoming messages
    sock.ev.on('messages.upsert', async ({ messages, type }) => {
        if (type !== 'notify' && type !== 'append') return;

        for (const msg of messages) {
            if (msg.key?.id && msg.message) {
                msgStore.set(msg.key.id, msg.message);
                if (msgStore.size > 2000) {
                    const firstKey = msgStore.keys().next().value;
                    msgStore.delete(firstKey);
                }
            }

            // Ignore if message is sent by ourselves
            if (msg.key.fromMe) continue;

            const remoteJid = msg.key.remoteJid || '';

            // Ignore status broadcasts and groups
            if (remoteJid.endsWith('@broadcast') || remoteJid.endsWith('@g.us')) {
                continue;
            }

            // Recursively unwrap nested wrappers (ephemeral, viewOnce, edited, etc.)
            let m = msg.message;
            while (m?.ephemeralMessage || m?.viewOnceMessage || m?.viewOnceMessageV2 || m?.documentWithCaptionMessage || m?.editedMessage) {
                m = m.ephemeralMessage?.message 
                    || m.viewOnceMessage?.message 
                    || m.viewOnceMessageV2?.message 
                    || m.documentWithCaptionMessage?.message
                    || m.editedMessage?.message?.protocolMessage?.editedMessage
                    || m.editedMessage?.message;
            }

            // Extract message body text
            const text = m?.conversation
                || m?.extendedTextMessage?.text
                || m?.imageMessage?.caption
                || m?.videoMessage?.caption
                || m?.buttonsResponseMessage?.selectedDisplayText
                || m?.templateButtonReplyMessage?.selectedId
                || m?.listResponseMessage?.title
                || '';

            const trimmed = text.trim();
            if (!trimmed) continue;

            // Extract real sender phone number (resolving LID if needed)
            let senderPhone = null;

            const phoneJid = msg.key.remoteJidAlt || msg.key.senderPn || msg.key.participantPn || msg.key.participantAlt;
            if (phoneJid && phoneJid.endsWith('@s.whatsapp.net')) {
                senderPhone = phoneJid.replace('@s.whatsapp.net', '').replace(/[^0-9]/g, '');
            }

            if (!senderPhone && remoteJid.endsWith('@s.whatsapp.net')) {
                senderPhone = remoteJid.replace('@s.whatsapp.net', '').replace(/[^0-9]/g, '');
            }

            if (!senderPhone) {
                // If it's Bos Joni's known WhatsApp LID, map directly to his phone number
                if (remoteJid.includes('249070069219550')) {
                    senderPhone = '083183494835';
                } else if (remoteJid.endsWith('@lid')) {
                    senderPhone = remoteJid; // Keep full @lid JID so formatJid preserves it
                } else {
                    senderPhone = remoteJid.replace('@s.whatsapp.net', '');
                }
            }

            const senderName = msg.pushName || '';
            const senderKey = String(senderPhone);
            const targetJid = remoteJid;

            console.log(`[WHATSAPP INCOMING] Message from ${senderPhone} (${senderName}): "${trimmed}"`);

            // Send presence update ("composing") so user sees "mengetik..."
            try {
                if (sock && targetJid) {
                    await sock.sendPresenceUpdate('composing', targetJid);
                }
            } catch (presErr) {
                // Ignore presence update error
            }

            // Debounce rapid sequential messages from the same sender
            const existingBuffer = incomingDebounceBuffers.get(senderKey);
            if (existingBuffer) {
                clearTimeout(existingBuffer.timer);
                existingBuffer.messages.push(trimmed);
                if (senderName && (!existingBuffer.name || existingBuffer.name === 'Pengguna WhatsApp')) {
                    existingBuffer.name = senderName;
                }
                existingBuffer.timer = setTimeout(async () => {
                    const combinedText = existingBuffer.messages.join("\n");
                    const finalName = existingBuffer.name;
                    incomingDebounceBuffers.delete(senderKey);
                    await forwardToWebhook(senderKey, finalName, combinedText);
                }, DEBOUNCE_DELAY_MS);
            } else {
                const entry = {
                    messages: [trimmed],
                    name: senderName,
                    targetJid: targetJid,
                    timer: setTimeout(async () => {
                        const combinedText = entry.messages.join("\n");
                        const finalName = entry.name;
                        incomingDebounceBuffers.delete(senderKey);
                        await forwardToWebhook(senderKey, finalName, combinedText);
                    }, DEBOUNCE_DELAY_MS)
                };
                incomingDebounceBuffers.set(senderKey, entry);
            }
        }
    });
}

// Setup Express API Server
const app = express();
app.use(express.json());

// Auth middleware for internal API requests
function checkAuth(req, res, next) {
    const secret = req.headers['x-gateway-secret'] || req.body?.secret || req.query?.secret;
    if (GATEWAY_SECRET && secret !== GATEWAY_SECRET) {
        return res.status(401).json({ status: false, error: 'Unauthorized: Invalid gateway secret' });
    }
    next();
}

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        uptime: process.uptime(),
        connection: connectionState
    });
});

// Gateway status endpoint (for Laravel Admin Dashboard)
app.get('/status', (req, res) => {
    res.json({
        status: connectionState,
        qr: currentQrDataUrl,
        user: connectedUser,
        uptime: process.uptime(),
        timestamp: new Date().toISOString()
    });
});

// Send WhatsApp text message
app.post('/send', checkAuth, async (req, res) => {
    const { target, message } = req.body;

    if (!target || !message) {
        return res.status(400).json({ status: false, error: 'Missing target phone number or message content' });
    }

    if (connectionState !== 'connected' || !sock) {
        return res.status(503).json({
            status: false,
            error: `WhatsApp client is not connected (current state: ${connectionState})`
        });
    }

    try {
        const jid = formatJid(target);
        if (!jid) {
            return res.status(400).json({ status: false, error: 'Invalid phone number format' });
        }

        const sent = await sock.sendMessage(jid, { text: String(message) });
        if (sent?.key?.id && sent?.message) {
            msgStore.set(sent.key.id, sent.message);
        }
        console.log(`[WHATSAPP SENT] Message delivered to ${jid} (id: ${sent?.key?.id})`);

        return res.json({
            status: true,
            messageId: sent?.key?.id,
            target: jid
        });
    } catch (err) {
        console.error(`[WHATSAPP SEND ERROR] Failed sending to ${target}:`, err.message);
        return res.status(500).json({ status: false, error: err.message });
    }
});

// Logout / Disconnect WhatsApp account
app.post('/logout', checkAuth, async (req, res) => {
    try {
        console.log('[WHATSAPP] Logout requested via API.');
        if (sock) {
            try {
                sock.ev.removeAllListeners();
                await sock.logout();
            } catch (e) {}
            sock = null;
        }
        if (authAdapter) {
            await authAdapter.clearAuth();
            authAdapter = null;
        }
        connectionState = 'disconnected';
        currentQrDataUrl = null;
        connectedUser = null;

        setTimeout(startWhatsApp, 2000);

        return res.json({ status: true, message: 'WhatsApp session cleared and restarting...' });
    } catch (err) {
        console.error('[WHATSAPP LOGOUT ERROR]', err.message);
        return res.status(500).json({ status: false, error: err.message });
    }
});

// Reset chat sessions ratchets (clears Bad MAC / out-of-sync sessions while keeping QR login intact)
app.post('/reset-chat-sessions', checkAuth, async (req, res) => {
    try {
        console.log('[WHATSAPP] Resetting chat session ratchets via API...');
        if (authAdapter && authAdapter.clearChatSessions) {
            await authAdapter.clearChatSessions();
        }
        if (sock) {
            try {
                sock.ev.removeAllListeners();
                sock.end(new Error('Reset chat sessions'));
            } catch (e) {}
            sock = null;
        }
        setTimeout(startWhatsApp, 1500);
        return res.json({ status: true, message: 'Chat sessions reset successfully, login credentials preserved.' });
    } catch (err) {
        console.error('[WHATSAPP RESET CHAT SESSIONS ERROR]', err.message);
        return res.status(500).json({ status: false, error: err.message });
    }
});

// Restart connection
app.post('/restart', checkAuth, async (req, res) => {
    try {
        console.log('[WHATSAPP] Reconnection requested via API.');
        if (sock) {
            try {
                sock.ev.removeAllListeners();
                sock.end(new Error('Manual restart'));
            } catch (e) {}
            sock = null;
        }
        setTimeout(startWhatsApp, 1000);
        return res.json({ status: true, message: 'Reconnecting...' });
    } catch (err) {
        return res.status(500).json({ status: false, error: err.message });
    }
});

// Start listening & initiate WhatsApp socket
app.listen(PORT, '0.0.0.0', () => {
    console.log(`=======================================================`);
    console.log(`🚀 Rentify WhatsApp Gateway running on port ${PORT}`);
    console.log(`📡 Webhook destination: ${WEBHOOK_URL}`);
    console.log(`🔒 Secret verification: ${GATEWAY_SECRET ? 'Enabled' : 'Disabled'}`);
    console.log(`=======================================================`);
    startWhatsApp();
});
