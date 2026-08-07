import http from 'k6/http';
import { check, sleep } from 'k6';
import { Trend } from 'k6/metrics';

const baseUrl = (__ENV.K6_BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const username = __ENV.K6_USERNAME || '';
const password = __ENV.K6_PASSWORD || '';
const listDuration = new Trend('ticket_list_duration', true);
const createDuration = new Trend('ticket_create_duration', true);
let authenticated = false;
let csrfToken = '';

export const options = {
    scenarios: {
        browse: {
            executor: 'constant-vus',
            vus: Number(__ENV.K6_READ_VUS || 30),
            duration: __ENV.K6_DURATION || '2m',
            exec: 'browse',
        },
        create: {
            executor: 'constant-vus',
            vus: Number(__ENV.K6_WRITE_VUS || 5),
            duration: __ENV.K6_DURATION || '2m',
            startTime: '10s',
            exec: 'createTicket',
        },
    },
    thresholds: {
        ticket_list_duration: ['p(95)<2000'],
        ticket_create_duration: ['p(95)<1000'],
        checks: ['rate>0.99'],
    },
};

function login() {
    if (!username || !password) {
        return false;
    }

    const page = http.get(`${baseUrl}/login`, { tags: { operation: 'login-page' } });
    const tokenMatch = page.body && page.body.match(/name="_token"[^>]*value="([^"]+)"/);

    if (!tokenMatch) {
        return false;
    }

    csrfToken = tokenMatch[1];
    const form = `_token=${encodeURIComponent(csrfToken)}&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`;
    const response = http.post(`${baseUrl}/login`, form, {
        redirects: 0,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        tags: { operation: 'login' },
    });

    authenticated = check(response, {
        'login redirects': (result) => result.status === 302,
    });

    return authenticated;
}

export function browse() {
    if (!authenticated) {
        login();
    }

    const response = http.get(`${baseUrl}/tickets`, { tags: { operation: 'ticket-list' } });
    listDuration.add(response.timings.duration);
    check(response, {
        'ticket list responds': (result) => result.status === 200,
    });
    sleep(Number(__ENV.K6_THINK_TIME || 1));
}

export function createTicket() {
    if (!authenticated) {
        login();
    }

    const template = __ENV.K6_CREATE_FORM || '';

    if (!authenticated || template === '') {
        sleep(1);
        return;
    }

    const form = `${template}&_token=${encodeURIComponent(csrfToken)}`;
    const response = http.post(`${baseUrl}/tickets`, form, {
        redirects: 0,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        tags: { operation: 'ticket-create' },
    });
    createDuration.add(response.timings.duration);
    check(response, {
        'ticket create redirects': (result) => result.status === 302,
    });
    sleep(Number(__ENV.K6_THINK_TIME || 1));
}
