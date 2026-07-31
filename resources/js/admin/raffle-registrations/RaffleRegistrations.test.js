import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, describe, expect, it, vi } from 'vitest';
import RaffleRegistrations from './RaffleRegistrations.vue';
import { mountRaffleRegistrations } from '../../app.js';
import { validateSnapshot } from './snapshot.js';

const url = (page = 1) => `${window.location.origin}/raffles/7/registrations${page === 1 ? '' : `?page=${page}`}`;
const snapshot = (page = 1, canonical = url(page)) => ({
    version: 1,
    raffleId: 7,
    rows: [{
        id: page,
        name: `Guest ${page}`,
        email: `guest${page}@example.com`,
        status: 'active',
        statusLabel: 'Activa',
        createdAt: '2026-07-31 10:00',
        linkedAccount: false,
        linkedAccountLabel: 'Sin cuenta vinculada',
        actions: [{ kind: 'flag', label: 'Marcar', confirm: '¿Marcar?', url: `${url(page)}/1/flag`, method: 'POST' }],
    }],
    counts: { active: 3, flagged: 0, cancelled: 0, total: 3 },
    pagination: {
        current: page,
        last: 3,
        perPage: 25,
        total: 3,
        canonicalUrl: canonical,
        links: [1, 2, 3].map(value => ({ page: value, url: url(value), current: value === page })),
    },
    loginUrl: `${window.location.origin}/login`,
    copy: {
        paginationLabel: 'Páginas',
        page: 'Página',
        navigationError: 'No se pudo cargar la página.',
        mutationError: 'No se pudo confirmar la acción.',
        reconciliationError: 'No se pudo recuperar el estado.',
        retryLabel: 'Reintentar',
        sessionExpired: 'La sesión venció.',
        loginLabel: 'Iniciar sesión',
        summary: { active: 'Activas', flagged: 'Para revisión', cancelled: 'Canceladas', total: 'Total' },
    },
});
const response = (body, status = 200) => ({ ok: status >= 200 && status < 300, status, json: async () => body });
const deferred = () => {
    let resolve;
    const promise = new Promise(done => { resolve = done; });
    return { promise, resolve };
};
const mounted = [];
const render = (initial = snapshot()) => {
    document.body.innerHTML = '<h1 id="registrations-heading" tabindex="-1">Inscripciones</h1>';
    const wrapper = mount(RaffleRegistrations, { attachTo: document.body, props: { initial, headingId: 'registrations-heading', csrfToken: 'token' } });
    mounted.push(wrapper);
    return wrapper;
};

afterEach(() => {
    mounted.splice(0).forEach(wrapper => wrapper.unmount());
    document.querySelectorAll('[data-v-app]').forEach(root => root.__vue_app__?.unmount());
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
    document.body.innerHTML = '';
});

describe('hydration schema', () => {
    it('accepts a complete snapshot and rejects incomplete nested data', () => {
        expect(validateSnapshot(snapshot(), 7)).toEqual(snapshot());
        for (const malformed of [
            { ...snapshot(), version: 2 },
            { ...snapshot(), raffleId: 8 },
            { ...snapshot(), rows: [{ ...snapshot().rows[0], email: null }] },
            { ...snapshot(), rows: [{ ...snapshot().rows[0], actions: [{ ...snapshot().rows[0].actions[0], kind: 'delete' }] }] },
            { ...snapshot(), pagination: { ...snapshot().pagination, links: [] } },
            { ...snapshot(), copy: { page: 'Página' } },
        ]) expect(validateSnapshot(malformed, 7)).toBeNull();
    });

    it('leaves fallback markup untouched unless JSON and schema both validate', async () => {
        document.body.innerHTML = '<div id="raffle-registration-list" data-raffle-id="7">Fallback</div><script id="raffle-registration-snapshot" type="application/json">{"version":2}</script>';
        expect(mountRaffleRegistrations()).toBe(false);
        expect(document.querySelector('#raffle-registration-list').textContent).toBe('Fallback');
        document.querySelector('#raffle-registration-snapshot').textContent = JSON.stringify(snapshot());
        expect(mountRaffleRegistrations()).toBe(true);
        await flushPromises();
        expect(document.body.textContent).toContain('Guest 1');
    });
});

describe('navigation state machine', () => {
    it('aborts superseded GETs and lets only the latest clicked page commit and push', async () => {
        const first = deferred();
        const second = deferred();
        const fetch = vi.fn().mockReturnValueOnce(first.promise).mockReturnValueOnce(second.promise);
        vi.stubGlobal('fetch', fetch);
        const push = vi.spyOn(history, 'pushState');
        const wrapper = render();
        await wrapper.get('[data-page="2"]').trigger('click');
        await wrapper.get('[data-page="3"]').trigger('click');
        expect(fetch.mock.calls[0][1].signal.aborted).toBe(true);
        second.resolve(response(snapshot(3)));
        await flushPromises();
        first.resolve(response(snapshot(2)));
        await flushPromises();
        expect(wrapper.text()).toContain('Guest 3');
        expect(wrapper.text()).not.toContain('Guest 2');
        expect(push).toHaveBeenCalledOnce();
        expect(push).toHaveBeenCalledWith({}, '', url(3));
    });

    it('replaces a server-canonicalized click and performs no request or history write for the same URL', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue(response(snapshot(3, url(3)))));
        const replace = vi.spyOn(history, 'replaceState');
        const push = vi.spyOn(history, 'pushState');
        const wrapper = render();
        await wrapper.get('[data-page="2"]').trigger('click');
        await flushPromises();
        expect(replace).toHaveBeenCalledWith({}, '', url(3));
        expect(push).not.toHaveBeenCalled();
        await wrapper.get('[data-page="3"]').trigger('click');
        expect(fetch).toHaveBeenCalledOnce();
        expect(replace).toHaveBeenCalledOnce();
    });

    it('commits only the latest deferred popstate and never writes history', async () => {
        const first = deferred();
        const second = deferred();
        vi.stubGlobal('fetch', vi.fn().mockReturnValueOnce(first.promise).mockReturnValueOnce(second.promise));
        const push = vi.spyOn(history, 'pushState');
        const replace = vi.spyOn(history, 'replaceState');
        const wrapper = render();
        history.replaceState({}, '', url(2));
        dispatchEvent(new PopStateEvent('popstate'));
        history.replaceState({}, '', url(3));
        dispatchEvent(new PopStateEvent('popstate'));
        replace.mockClear();
        second.resolve(response(snapshot(3)));
        await flushPromises();
        first.resolve(response(snapshot(2)));
        await flushPromises();
        expect(wrapper.text()).toContain('Guest 3');
        expect(push).not.toHaveBeenCalled();
        expect(replace).not.toHaveBeenCalled();
    });

    it('focuses and announces success but preserves focus/data and announces invalid failure', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValueOnce(response(snapshot(2))).mockResolvedValueOnce(response({ bad: true })));
        const wrapper = render();
        await wrapper.get('[data-page="2"]').trigger('click');
        await flushPromises();
        expect(document.activeElement.id).toBe('registrations-heading');
        expect(wrapper.get('[aria-live="polite"]').text()).toBe('Página 2 / 3');
        document.body.focus();
        await wrapper.get('[data-page="3"]').trigger('click');
        await flushPromises();
        expect(wrapper.text()).toContain('Guest 2');
        expect(document.activeElement.id).toBe('registrations-heading');
        expect(wrapper.get('[aria-live="polite"]').text()).toBe('No se pudo cargar la página.');
    });

    it.each([401, 419])('makes navigation %i terminal without later commits or history writes', async status => {
        const late = deferred();
        vi.stubGlobal('fetch', vi.fn().mockResolvedValueOnce(response(null, status)).mockReturnValueOnce(late.promise));
        const push = vi.spyOn(history, 'pushState');
        const wrapper = render();
        await wrapper.get('[data-page="2"]').trigger('click');
        await flushPromises();
        expect(wrapper.text()).toContain('La sesión venció.');
        expect(wrapper.get(`a[href="${window.location.origin}/login"]`).text()).toBe('Iniciar sesión');
        await wrapper.get('[data-page="3"]').trigger('click');
        late.resolve(response(snapshot(3)));
        await flushPromises();
        expect(fetch).toHaveBeenCalledOnce();
        expect(wrapper.text()).toContain('Guest 1');
        expect(push).not.toHaveBeenCalled();
    });
});

describe('mutation and reconciliation state machine', () => {
    it.each([200, 409])('keeps confirmed data while a %i action is pending and commits its authoritative response once', async status => {
        const pending = deferred();
        const fetch = vi.fn().mockReturnValue(pending.promise);
        vi.stubGlobal('fetch', fetch);
        vi.stubGlobal('confirm', vi.fn(() => true));
        const wrapper = render();
        await wrapper.get('form').trigger('submit');
        await wrapper.get('form').trigger('submit');
        expect(wrapper.text()).toContain('Guest 1');
        expect(fetch).toHaveBeenCalledOnce();
        pending.resolve(response({ snapshot: { ...snapshot(), rows: [{ ...snapshot().rows[0], name: 'Confirmed Guest' }] }, feedback: 'Estado confirmado.' }, status));
        await flushPromises();
        expect(wrapper.text()).toContain('Confirmed Guest');
        expect(wrapper.get('[aria-live="polite"]').text()).toBe('Estado confirmado.');
    });

    it.each([
        ['422', () => response({ errors: {} }, 422)],
        ['non-JSON response', () => ({ ok: true, status: 200, json: async () => { throw new SyntaxError('html'); } })],
        ['network loss', () => Promise.reject(new TypeError('offline'))],
    ])('reconciles %s once without repeating POST', async (label, uncertain) => {
        const fetch = vi.fn().mockImplementationOnce(uncertain).mockResolvedValueOnce(response({ ...snapshot(), rows: [{ ...snapshot().rows[0], name: 'Reconciled Guest' }] }));
        vi.stubGlobal('fetch', fetch);
        vi.stubGlobal('confirm', vi.fn(() => true));
        const wrapper = render();
        await wrapper.get('form').trigger('submit');
        await flushPromises();
        expect(wrapper.text()).toContain('Reconciled Guest');
        expect(fetch).toHaveBeenCalledTimes(2);
        expect(fetch.mock.calls.map(call => call[1].method ?? 'GET')).toEqual(['POST', 'GET']);
    });

    it('blocks after failed reconciliation, retries with GET only, then consumes the latest deferred popstate once', async () => {
        const retry = deferred();
        const deferredPage = deferred();
        const fetch = vi.fn().mockRejectedValueOnce(new TypeError('post lost')).mockRejectedValueOnce(new TypeError('get lost'))
            .mockReturnValueOnce(retry.promise).mockReturnValueOnce(deferredPage.promise);
        vi.stubGlobal('fetch', fetch);
        vi.stubGlobal('confirm', vi.fn(() => true));
        const wrapper = render();
        await wrapper.get('form').trigger('submit');
        history.replaceState({}, '', url(2)); dispatchEvent(new PopStateEvent('popstate'));
        history.replaceState({}, '', url(3)); dispatchEvent(new PopStateEvent('popstate'));
        await flushPromises();
        expect(wrapper.get('button[data-retry]').text()).toBe('Reintentar');
        await wrapper.get('button[data-retry]').trigger('click');
        retry.resolve(response(snapshot())); await flushPromises();
        deferredPage.resolve(response(snapshot(3))); await flushPromises();
        expect(wrapper.text()).toContain('Guest 3');
        expect(fetch.mock.calls.map(call => call[1].method ?? 'GET')).toEqual(['POST', 'GET', 'GET', 'GET']);
    });

    it.each([401, 419])('makes mutation %i terminal without reconciliation or data loss', async status => {
        const fetch = vi.fn().mockResolvedValue(response(null, status));
        vi.stubGlobal('fetch', fetch);
        vi.stubGlobal('confirm', vi.fn(() => true));
        const wrapper = render();
        await wrapper.get('form').trigger('submit'); await flushPromises();
        expect(wrapper.text()).toContain('Guest 1');
        expect(wrapper.text()).toContain('La sesión venció.');
        expect(fetch).toHaveBeenCalledOnce();
    });

    it.each([[401, 'reconciliation'], [419, 'reconciliation'], [401, 'retry'], [419, 'retry']])('makes %s during %s terminal', async (status, stage) => {
        const fetch = vi.fn().mockRejectedValueOnce(new TypeError('post lost'));
        if (stage === 'retry') fetch.mockRejectedValueOnce(new TypeError('get lost'));
        fetch.mockResolvedValueOnce(response(null, status));
        vi.stubGlobal('fetch', fetch);
        vi.stubGlobal('confirm', vi.fn(() => true));
        const wrapper = render();
        await wrapper.get('form').trigger('submit'); await flushPromises();
        if (stage === 'retry') {
            await wrapper.get('button[data-retry]').trigger('click'); await flushPromises();
        }
        expect(wrapper.text()).toContain('La sesión venció.');
        expect(wrapper.text()).toContain('Guest 1');
    });
});
