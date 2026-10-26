// whatsapp-gateway/test_gateway.js
const http = require('http');

const PORT = 3001; // Port uji
process.env.WHATSAPP_GATEWAY_PORT = String(PORT);
process.env.WHATSAPP_GATEWAY_SECRET = 'rentify-wa-secret-2026';
process.env.GATEWAY_SANDBOX_MODE = 'true';

const server = require('./server'); // We will export or run server

function request(options, postData = null) {
    return new Promise((resolve, reject) => {
        const req = http.request(options, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                let json = null;
                try {
                    json = JSON.parse(data);
                } catch (e) {
                    json = null;
                }
                resolve({
                    statusCode: res.statusCode,
                    headers: res.headers,
                    body: data,
                    json: json
                });
            });
        });

        req.on('error', err => reject(err));

        if (postData) {
            req.write(typeof postData === 'string' ? postData : JSON.stringify(postData));
        }
        req.end();
    });
}

async function runTests() {
    console.log('--- RUNNING WHATSAPP GATEWAY INTEGRATION TESTS ---');
    let failures = 0;

    // Test 1: POST /send without secret should be 401
    try {
        const res = await request({
            hostname: '127.0.0.1',
            port: PORT,
            path: '/send',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        }, { target: '081234567890', message: 'Test without secret' });

        if (res.statusCode === 401) {
            console.log('PASS: POST /send without secret rejected with 401');
        } else {
            console.error(`FAIL: Expected 401, got ${res.statusCode}`);
            failures++;
        }
    } catch (e) {
        console.error('FAIL test 1 exception:', e.message);
        failures++;
    }

    // Test 2: GET /status should return valid status structure
    try {
        const res = await request({
            hostname: '127.0.0.1',
            port: PORT,
            path: '/status',
            method: 'GET'
        });

        if (res.statusCode === 200 && res.json && typeof res.json.status !== 'undefined' && typeof res.json.paired !== 'undefined') {
            console.log('PASS: GET /status returned valid payload with paired property');
        } else {
            console.error('FAIL: GET /status did not return valid status/paired payload:', res.body);
            failures++;
        }
    } catch (e) {
        console.error('FAIL test 2 exception:', e.message);
        failures++;
    }

    // Test 3: GET /qr without auth should be 401, with auth should return HTML wrapper
    try {
        const unauthRes = await request({
            hostname: '127.0.0.1',
            port: PORT,
            path: '/qr',
            method: 'GET'
        });

        if (unauthRes.statusCode === 401) {
            console.log('PASS: GET /qr without auth rejected with 401');
        } else {
            console.error(`FAIL: Expected 401 for unauth /qr, got ${unauthRes.statusCode}`);
            failures++;
        }

        const authRes = await request({
            hostname: '127.0.0.1',
            port: PORT,
            path: '/qr',
            method: 'GET',
            headers: { 'X-Gateway-Secret': 'rentify-wa-secret-2026' }
        });

        if (authRes.statusCode === 200 && (authRes.body.includes('<img') || authRes.body.includes('WhatsApp QR') || authRes.body.includes('connected'))) {
            console.log('PASS: GET /qr with auth rendered HTML visual wrapper');
        } else {
            console.error('FAIL: GET /qr with auth did not render HTML visual wrapper:', authRes.statusCode, authRes.body.substring(0, 100));
            failures++;
        }
    } catch (e) {
        console.error('FAIL test 3 exception:', e.message);
        failures++;
    }

    // Test 4: POST /send in SANDBOX mode returns deterministic mock success
    try {
        const res = await request({
            hostname: '127.0.0.1',
            port: PORT,
            path: '/send',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Gateway-Secret': 'rentify-wa-secret-2026'
            }
        }, { target: '081234567890', message: 'Hello Sandbox' });

        if (res.statusCode === 200 && res.json && res.json.status === true && String(res.json.messageId).startsWith('mock-')) {
            console.log('PASS: POST /send in sandbox returned deterministic mock response');
        } else {
            console.error('FAIL: POST /send in sandbox did not return mock success:', res.statusCode, res.body);
            failures++;
        }
    } catch (e) {
        console.error('FAIL test 4 exception:', e.message);
        failures++;
    }

    console.log(`TOTAL FAILURES: ${failures}`);
    process.exit(failures > 0 ? 1 : 0);
}

// Give server time to bind then run tests
setTimeout(runTests, 1000);
