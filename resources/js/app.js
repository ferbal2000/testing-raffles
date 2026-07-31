import { createApp } from 'vue';
import RaffleRegistrations from './admin/raffle-registrations/RaffleRegistrations.vue';
import { validateSnapshot } from './admin/raffle-registrations/snapshot.js';

export function mountRaffleRegistrations() {
    const root = document.getElementById('raffle-registration-list');
    const source = document.getElementById('raffle-registration-snapshot');
    if (!root || !source) return false;
    let parsed;
    try { parsed = JSON.parse(source.textContent); } catch { return false; }
    const snapshot = validateSnapshot(parsed, Number(root.dataset.raffleId));
    if (!snapshot) return false;
    createApp(RaffleRegistrations, {
        initial: snapshot,
        headingId: 'raffle-registration-heading',
        csrfToken: root.dataset.csrf,
    }).mount(root);
    return true;
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mountRaffleRegistrations);
else mountRaffleRegistrations();
