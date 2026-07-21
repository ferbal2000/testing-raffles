import { enableAutoUnmount, flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import RaffleRegistrations from './RaffleRegistrations.vue';
import { mountRaffleRegistrations } from '../../app.js';

enableAutoUnmount(afterEach);

const row = (id) => ({
    id,
    name: `Person ${id}`,
    email: `person${id}@example.test`,
    status: { value: 'active', label: 'Activa' },
    created_at: '2026-07-21 12:30',
    linked_account: { value: false, label: 'Sin cuenta vinculada' },
    actions: [{ kind: 'flag', label: 'Marcar', confirm: '¿Marcar?', url: `/registrations/${id}/flag` }],
});

const snapshot = (page = 1, overrides = {}) => ({
    raffle: { id: 7 },
    rows: [row(page * 10 + 1), row(page * 10 + 2)],
    counts: { active: 27, flagged: 2, cancelled: 1, total: 30 },
    pagination: {
        current_page: page,
        last_page: 2,
        per_page: 25,
        from: page === 1 ? 1 : 26,
        to: page === 1 ? 25 : 30,
        canonical_url: `/raffles/7/registrations?page=${page}`,
        links: [1, 2].map((number) => ({
            page: number,
            url: `/raffles/7/registrations?page=${number}`,
            current: number === page,
        })),
    },
    copy: { busy: 'Procesando. Esperá un momento.', login_url: '/login', unavailable: 'No disponible.' },
    ...overrides,
});

const response = (value) => ({ ok: true, json: async () => ({ snapshot: value, feedback: null }) });
const deferred = () => {
    let resolve;
    let reject;
    const promise = new Promise((accept, decline) => { resolve = accept; reject = decline; });
    return { promise, resolve, reject };
};

describe('RaffleRegistrations navigation', () => {
    beforeEach(() => {
        window.history.replaceState({}, '', '/raffles/7/registrations?page=1');
        vi.stubGlobal('fetch', vi.fn());
    });

    afterEach(() => {
        vi.restoreAllMocks();
        vi.unstubAllGlobals();
        document.body.innerHTML = '';
    });

    it('renders real page links, the result range, and current-page semantics without mutation controls', () => {
        const wrapper = mount(RaffleRegistrations, { props: { initialSnapshot: snapshot() } });

        const links = wrapper.get('nav[aria-label="Paginación de inscripciones"]').findAll('a');
        expect(links.map((link) => link.attributes('href'))).toEqual([
            '/raffles/7/registrations?page=1',
            '/raffles/7/registrations?page=2',
        ]);
        expect(links[0].attributes('aria-current')).toBe('page');
        expect(wrapper.get('[data-results-range]').text()).toBe('Resultados 1–25 de 30');
        expect(wrapper.findAll('button')).toHaveLength(0);
    });

    it('fetches before pushState, commits the authoritative page, then focuses and announces the results', async () => {
        const pending = deferred();
        fetch.mockReturnValueOnce(pending.promise);
        const push = vi.spyOn(window.history, 'pushState');
        const wrapper = mount(RaffleRegistrations, { attachTo: document.body, props: { initialSnapshot: snapshot() } });

        await wrapper.get('a[href$="page=2"]').trigger('click');
        expect(push).not.toHaveBeenCalled();
        pending.resolve(response(snapshot(2)));
        await flushPromises();

        expect(push).toHaveBeenCalledWith({}, '', '/raffles/7/registrations?page=2');
        expect(wrapper.get('[data-results-heading]').element).toBe(document.activeElement);
        expect(wrapper.get('[aria-live="polite"]').text()).toBe('Página 2 cargada. Resultados 26–30 de 30.');
        expect(wrapper.text()).toContain('Person 21');
    });

    it('replaces a canonicalized URL and handles popstate without adding history', async () => {
        fetch
            .mockResolvedValueOnce(response(snapshot(2)))
            .mockResolvedValueOnce(response(snapshot(1)));
        const push = vi.spyOn(window.history, 'pushState');
        const replace = vi.spyOn(window.history, 'replaceState');
        const wrapper = mount(RaffleRegistrations, { props: { initialSnapshot: snapshot() } });

        await wrapper.get('a[href$="page=2"]').trigger('click');
        await flushPromises();
        expect(push).toHaveBeenCalledOnce();

        window.history.replaceState({}, '', '/raffles/7/registrations?page=99');
        window.dispatchEvent(new PopStateEvent('popstate'));
        await flushPromises();
        expect(push).toHaveBeenCalledOnce();
        expect(replace).toHaveBeenLastCalledWith({}, '', '/raffles/7/registrations?page=1');
        expect(wrapper.text()).toContain('Person 11');
    });

    it('aborts superseded GETs and rejects a late response', async () => {
        const oldRequest = deferred();
        fetch.mockReturnValueOnce(oldRequest.promise).mockResolvedValueOnce(response(snapshot(1)));
        const wrapper = mount(RaffleRegistrations, { props: { initialSnapshot: snapshot() } });

        window.history.replaceState({}, '', '/raffles/7/registrations?page=2');
        window.dispatchEvent(new PopStateEvent('popstate'));
        await flushPromises();
        const firstSignal = fetch.mock.calls[0][1].signal;

        window.history.replaceState({}, '', '/raffles/7/registrations?page=1');
        window.dispatchEvent(new PopStateEvent('popstate'));
        await flushPromises();
        oldRequest.resolve(response(snapshot(2)));
        await flushPromises();

        expect(firstSignal.aborted).toBe(true);
        expect(wrapper.text()).toContain('Person 11');
        expect(wrapper.text()).not.toContain('Person 21');
    });

    it('preserves confirmed data after failure and retries the same page', async () => {
        fetch.mockResolvedValueOnce({ ok: true, json: async () => ({ unexpected: true }) }).mockResolvedValueOnce(response(snapshot(2)));
        const wrapper = mount(RaffleRegistrations, { props: { initialSnapshot: snapshot() } });

        await wrapper.get('a[href$="page=2"]').trigger('click');
        await flushPromises();
        expect(wrapper.get('[role="alert"]').text()).toContain('No se pudo cargar la página');
        expect(wrapper.text()).toContain('Person 11');

        await wrapper.get('button').trigger('click');
        await flushPromises();
        expect(fetch).toHaveBeenCalledTimes(2);
        expect(wrapper.text()).toContain('Person 21');
    });

    it('rejects an incomplete snapshot before replacing confirmed results and offers retry', async () => {
        const incomplete = { pagination: snapshot(2).pagination };
        fetch.mockResolvedValueOnce(response(incomplete)).mockResolvedValueOnce(response(snapshot(2)));
        const wrapper = mount(RaffleRegistrations, { props: { initialSnapshot: snapshot() } });

        await wrapper.get('a[href$="page=2"]').trigger('click');
        await flushPromises();
        expect(wrapper.get('[role="alert"]').text()).toContain('No se pudo cargar la página');
        expect(wrapper.text()).toContain('Person 11');
        expect(wrapper.get('button').text()).toBe('Reintentar');

        await wrapper.get('button').trigger('click');
        await flushPromises();
        expect(wrapper.text()).toContain('Person 21');
    });

    it('globally disables in-screen navigation while retaining visible data and busy context', async () => {
        fetch.mockReturnValueOnce(new Promise(() => {}));
        const wrapper = mount(RaffleRegistrations, { props: { initialSnapshot: snapshot() } });

        await wrapper.get('a[href$="page=2"]').trigger('click');

        expect(wrapper.get('[data-registration-screen]').attributes('aria-busy')).toBe('true');
        expect(wrapper.get('[data-back-link]').attributes('aria-disabled')).toBe('true');
        expect(wrapper.get('a[href$="page=2"]').attributes('aria-disabled')).toBe('true');
        expect(wrapper.text()).toContain('Procesando. Esperá un momento.');
        expect(wrapper.text()).toContain('Person 11');
    });

    it('runs the Unit 2 runtime harness from server JSON through history and retry', async () => {
        document.body.innerHTML = '<section id="raffle-registration-app">Blade fallback</section>'
            + `<script id="raffle-registration-snapshot" type="application/json">${JSON.stringify(snapshot())}</script>`;
        const firstPageChange = deferred();
        fetch
            .mockReturnValueOnce(firstPageChange.promise)
            .mockResolvedValueOnce(response(snapshot(1)))
            .mockResolvedValueOnce(response(snapshot(2)))
            .mockRejectedValueOnce(new TypeError('offline'))
            .mockResolvedValueOnce(response(snapshot(1)));

        const app = mountRaffleRegistrations(document);
        await flushPromises();
        expect(app).not.toBeNull();
        expect(document.querySelector('[data-results-range]').textContent).toBe('Resultados 1–25 de 30');
        expect(document.body.textContent).not.toContain('Blade fallback');

        document.querySelector('a[href$="page=2"]').click();
        await flushPromises();
        expect(document.querySelector('[data-registration-screen]').getAttribute('aria-busy')).toBe('true');
        expect(document.body.textContent).toContain('Person 11');
        firstPageChange.resolve(response(snapshot(2)));
        await flushPromises();
        expect(document.activeElement).toBe(document.querySelector('[data-results-heading]'));
        expect(document.querySelector('[aria-live="polite"]').textContent).toContain('Página 2 cargada');

        let popped = new Promise((resolve) => window.addEventListener('popstate', resolve, { once: true }));
        window.history.back();
        await popped;
        await flushPromises();
        expect(document.body.textContent).toContain('Person 11');

        popped = new Promise((resolve) => window.addEventListener('popstate', resolve, { once: true }));
        window.history.forward();
        await popped;
        await flushPromises();
        expect(document.body.textContent).toContain('Person 21');

        document.querySelector('a[href$="page=1"]').click();
        await flushPromises();
        expect(document.querySelector('[role="alert"]').textContent).toContain('No se pudo cargar');
        expect(document.body.textContent).toContain('Person 21');
        document.querySelector('button').click();
        await flushPromises();
        expect(document.body.textContent).toContain('Person 11');
        app.unmount();
    });

    it('leaves the Blade fallback intact when initial snapshot parsing fails', () => {
        document.body.innerHTML = '<section id="raffle-registration-app">Blade fallback</section>'
            + '<script id="raffle-registration-snapshot" type="application/json">invalid</script>';

        expect(mountRaffleRegistrations(document)).toBeNull();
        expect(document.body.textContent).toContain('Blade fallback');
    });
});
