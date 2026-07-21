import { createApp } from 'vue';

import RaffleRegistrations from './admin/raffle-registrations/RaffleRegistrations.vue';

export function mountRaffleRegistrations(root = document) {
    const element = root.querySelector('#raffle-registration-app');
    const data = root.querySelector('#raffle-registration-snapshot');
    if (!element || !data) return null;

    try {
        const snapshot = JSON.parse(data.textContent);
        const app = createApp(RaffleRegistrations, { initialSnapshot: snapshot });
        app.mount(element);
        return app;
    } catch {
        return null;
    }
}

mountRaffleRegistrations();
