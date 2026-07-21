<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    initialSnapshot: { type: Object, required: true },
});

const snapshot = ref(props.initialSnapshot);
const busy = ref(false);
const error = ref('');
const announcement = ref('');
const resultsHeading = ref(null);
const retry = ref(null);
let requestSequence = 0;
let activeController;

const range = computed(() => {
    const { from, to } = snapshot.value.pagination;
    return from === null ? 'Sin resultados' : `Resultados ${from}–${to} de ${snapshot.value.counts.total}`;
});

const urlPath = (url) => {
    const resolved = new URL(url, window.location.href);
    return `${resolved.pathname}${resolved.search}`;
};

const isRecord = (value) => value !== null && typeof value === 'object';
const isCompleteSnapshot = (value) => isRecord(value)
    && isRecord(value.raffle) && Number.isInteger(value.raffle.id)
    && Array.isArray(value.rows) && value.rows.every((row) => isRecord(row)
        && Number.isInteger(row.id) && typeof row.name === 'string' && typeof row.email === 'string'
        && isRecord(row.status) && typeof row.status.label === 'string'
        && isRecord(row.linked_account) && typeof row.linked_account.label === 'string'
        && Array.isArray(row.actions))
    && isRecord(value.counts) && ['active', 'flagged', 'cancelled', 'total'].every((key) => Number.isInteger(value.counts[key]))
    && isRecord(value.pagination)
    && ['current_page', 'last_page', 'per_page'].every((key) => Number.isInteger(value.pagination[key]))
    && ['from', 'to'].every((key) => value.pagination[key] === null || Number.isInteger(value.pagination[key]))
    && typeof value.pagination.canonical_url === 'string' && Array.isArray(value.pagination.links)
    && value.pagination.links.every((link) => isRecord(link) && Number.isInteger(link.page)
        && typeof link.url === 'string' && typeof link.current === 'boolean')
    && isRecord(value.copy) && ['busy', 'login_url', 'unavailable'].every((key) => typeof value.copy[key] === 'string');

async function loadPage(url, historyMode = 'push') {
    const sequence = ++requestSequence;
    activeController?.abort();
    activeController = new AbortController();
    busy.value = true;
    error.value = '';

    try {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
            signal: activeController.signal,
        });
        if (!response.ok) throw new Error(`Pagination failed (${response.status})`);
        const body = await response.json();
        if (sequence !== requestSequence) return;
        if (!isCompleteSnapshot(body?.snapshot)) throw new Error('Invalid pagination response');

        snapshot.value = body.snapshot;
        retry.value = null;
        const canonical = body.snapshot.pagination.canonical_url;
        const canonicalized = urlPath(canonical) !== urlPath(url);
        if (historyMode === 'push') {
            window.history[canonicalized ? 'replaceState' : 'pushState']({}, '', canonical);
        } else if (canonicalized) {
            window.history.replaceState({}, '', canonical);
        }

        announcement.value = `Página ${body.snapshot.pagination.current_page} cargada. ${range.value}.`;
        await nextTick();
        resultsHeading.value?.focus();
    } catch (caught) {
        if (sequence !== requestSequence || caught?.name === 'AbortError') return;
        retry.value = { url, historyMode };
        error.value = 'No se pudo cargar la página. Los resultados confirmados siguen visibles.';
    } finally {
        if (sequence === requestSequence) busy.value = false;
    }
}

function follow(event, link) {
    if (busy.value) return;
    loadPage(link.url);
}

function onPopState() {
    loadPage(window.location.href, 'pop');
}

onMounted(() => window.addEventListener('popstate', onPopState));
onBeforeUnmount(() => {
    window.removeEventListener('popstate', onPopState);
    activeController?.abort();
});
</script>

<template>
    <section data-registration-screen :aria-busy="busy ? 'true' : 'false'" class="w-full max-w-3xl space-y-6 rounded-2xl bg-white p-8">
        <header class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold">Inscripciones del sorteo #{{ snapshot.raffle.id }}</h1>
                <p>Revisá las inscripciones registradas para este sorteo.</p>
            </div>
            <a data-back-link href="/raffles" :aria-disabled="busy ? 'true' : undefined" :tabindex="busy ? -1 : undefined" @click="busy && $event.preventDefault()">Volver al listado</a>
        </header>

        <p v-if="busy" role="status">{{ snapshot.copy.busy }}</p>
        <div v-if="error" role="alert">
            {{ error }}
            <button type="button" :disabled="busy" @click="loadPage(retry.url, retry.historyMode)">Reintentar</button>
        </div>

        <section aria-labelledby="registration-summary-title">
            <h2 id="registration-summary-title">Resumen</h2>
            <dl>
                <div><dt>Activas</dt><dd>{{ snapshot.counts.active }}</dd></div>
                <div><dt>Para revisión</dt><dd>{{ snapshot.counts.flagged }}</dd></div>
                <div><dt>Canceladas</dt><dd>{{ snapshot.counts.cancelled }}</dd></div>
                <div><dt>Total registradas</dt><dd>{{ snapshot.counts.total }}</dd></div>
            </dl>
        </section>

        <section aria-labelledby="registration-results-title">
            <h2 id="registration-results-title" ref="resultsHeading" data-results-heading tabindex="-1">Inscripciones</h2>
            <p data-results-range>{{ range }}</p>
            <p aria-live="polite" aria-atomic="true">{{ announcement }}</p>

            <p v-if="snapshot.rows.length === 0">Todavía no hay inscripciones para este sorteo.</p>
            <table v-else>
                <thead><tr><th>Nombre</th><th>Email</th><th>Estado</th><th>Registrada</th><th>Cuenta</th><th>Acciones</th></tr></thead>
                <tbody>
                    <tr v-for="registration in snapshot.rows" :key="registration.id">
                        <td>{{ registration.name }}</td><td>{{ registration.email }}</td>
                        <td>{{ registration.status.label }}</td><td>{{ registration.created_at }}</td>
                        <td>{{ registration.linked_account.label }}</td><td>Acciones no disponibles</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <nav v-if="snapshot.pagination.last_page > 1" aria-label="Paginación de inscripciones">
            <a
                v-for="link in snapshot.pagination.links"
                :key="link.page"
                :href="link.url"
                :aria-current="link.current ? 'page' : undefined"
                :aria-disabled="busy ? 'true' : undefined"
                :tabindex="busy ? -1 : undefined"
                @click.prevent="follow($event, link)"
            >{{ link.page }}</a>
        </nav>
    </section>
</template>
