<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { validateSnapshot } from './snapshot.js';

const props = defineProps({ initial: Object, headingId: String, csrfToken: String });
const confirmed = ref(props.initial);
const mode = ref('idle');
const announcement = ref('');
let controller;
let generation = 0;
let deferredUrl;

const absolute = value => new URL(value, window.location.href).href;
const currentUrl = () => absolute(confirmed.value.pagination.canonicalUrl);
const active = token => token === generation && mode.value !== 'expired';
const announceSuccess = async () => {
    announcement.value = `${confirmed.value.copy.page} ${confirmed.value.pagination.current} / ${confirmed.value.pagination.last}`;
    await nextTick();
    document.getElementById(props.headingId)?.focus();
};
const expire = () => {
    generation += 1;
    controller?.abort();
    mode.value = 'expired';
    deferredUrl = undefined;
    announcement.value = confirmed.value.copy.sessionExpired;
};

async function navigate(target, source = 'click') {
    const requested = absolute(target);
    if (source === 'popstate' && ['mutating', 'reconciling', 'unresolved'].includes(mode.value)) {
        deferredUrl = requested;
        return;
    }
    if (['mutating', 'reconciling', 'unresolved', 'expired'].includes(mode.value) || requested === currentUrl()) return;
    controller?.abort();
    controller = new AbortController();
    const token = ++generation;
    mode.value = 'navigating';
    try {
        const response = await fetch(requested, { headers: { Accept: 'application/json' }, signal: controller.signal });
        if (!active(token)) return;
        if ([401, 419].includes(response.status)) return expire();
        if (!response.ok) throw new Error('Navigation failed');
        const next = validateSnapshot(await response.json(), confirmed.value.raffleId);
        if (!active(token)) return;
        if (!next) throw new Error('Invalid snapshot');
        confirmed.value = next;
        mode.value = 'idle';
        if (source === 'click') {
            const canonical = absolute(next.pagination.canonicalUrl);
            history[canonical === requested ? 'pushState' : 'replaceState']({}, '', canonical);
        }
        await announceSuccess();
    } catch (error) {
        if (!active(token) || error.name === 'AbortError') return;
        mode.value = 'idle';
        announcement.value = confirmed.value.copy.navigationError;
    }
}

async function consumeDeferred() {
    const target = deferredUrl;
    deferredUrl = undefined;
    if (target && target !== currentUrl()) await navigate(target, 'popstate');
}

async function reconcile(token) {
    mode.value = 'reconciling';
    try {
        const response = await fetch(currentUrl(), { headers: { Accept: 'application/json' } });
        if (!active(token)) return;
        if ([401, 419].includes(response.status)) return expire();
        if (!response.ok) throw new Error('Reconciliation failed');
        const next = validateSnapshot(await response.json(), confirmed.value.raffleId);
        if (!next) throw new Error('Invalid snapshot');
        confirmed.value = next;
        mode.value = 'idle';
        await consumeDeferred();
    } catch {
        if (!active(token)) return;
        mode.value = 'unresolved';
        announcement.value = confirmed.value.copy.reconciliationError;
    }
}

async function mutate(action) {
    if (mode.value !== 'idle' || !confirm(action.confirm)) return;
    controller?.abort();
    const token = ++generation;
    mode.value = 'mutating';
    try {
        const response = await fetch(action.url, {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': props.csrfToken },
            body: JSON.stringify({ page: confirmed.value.pagination.current }),
        });
        if (!active(token)) return;
        if ([401, 419].includes(response.status)) return expire();
        if (![200, 409].includes(response.status)) throw new Error('Uncertain mutation');
        const body = await response.json();
        const next = validateSnapshot(body.snapshot, confirmed.value.raffleId);
        if (!next || typeof body.feedback !== 'string') throw new Error('Invalid mutation');
        confirmed.value = next;
        announcement.value = body.feedback;
        mode.value = 'idle';
        await consumeDeferred();
    } catch {
        if (active(token)) await reconcile(token);
    }
}

const retry = () => mode.value === 'unresolved' && reconcile(++generation);

const onPopState = () => navigate(window.location.href, 'popstate');
onMounted(() => window.addEventListener('popstate', onPopState));
onBeforeUnmount(() => {
    generation += 1;
    controller?.abort();
    window.removeEventListener('popstate', onPopState);
});
</script>

<template>
    <div :aria-busy="mode !== 'idle'">
        <dl>
            <div v-for="key in ['active', 'flagged', 'cancelled', 'total']" :key="key">
                <dt>{{ confirmed.copy.summary[key] }}</dt><dd>{{ confirmed.counts[key] }}</dd>
            </div>
        </dl>
        <p v-if="confirmed.rows.length === 0">0</p>
        <table v-else>
            <tbody>
                <tr v-for="row in confirmed.rows" :key="row.id">
                    <td>{{ row.name }}</td><td>{{ row.email }}</td><td>{{ row.statusLabel }}</td>
                    <td>{{ row.createdAt }}</td><td>{{ row.linkedAccountLabel }}</td>
                    <td>
                        <form v-for="action in row.actions" :key="action.kind" :action="action.url" method="POST" @submit.prevent="mutate(action)">
                            <input type="hidden" name="_token" :value="csrfToken"><input type="hidden" name="page" :value="confirmed.pagination.current">
                            <button type="submit" :disabled="mode !== 'idle'">{{ action.label }}</button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
        <nav v-if="confirmed.pagination.last > 1" :aria-label="confirmed.copy.paginationLabel">
            <a v-for="link in confirmed.pagination.links" :key="link.page" :href="link.url" :data-page="link.page"
                :aria-current="link.current ? 'page' : undefined" @click.prevent="navigate(link.url)">{{ link.page }}</a>
        </nav>
        <button v-if="mode === 'unresolved'" data-retry type="button" @click="retry">{{ confirmed.copy.retryLabel }}</button>
        <a v-if="mode === 'expired'" :href="confirmed.loginUrl">{{ confirmed.copy.loginLabel }}</a>
        <p aria-live="polite">{{ announcement }}</p>
    </div>
</template>
