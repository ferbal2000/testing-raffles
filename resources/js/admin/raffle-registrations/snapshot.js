const text = value => typeof value === 'string' && value.length > 0;
const integer = value => Number.isInteger(value) && value >= 0;
const url = value => text(value);
const actionKinds = new Set(['flag', 'cancel', 'restore']);
const statuses = new Set(['active', 'flagged', 'cancelled']);

const validAction = action => action && typeof action === 'object'
    && actionKinds.has(action.kind)
    && ['label', 'confirm', 'url'].every(key => text(action[key]))
    && action.method === 'POST';

const validRow = row => row && typeof row === 'object'
    && integer(row.id) && row.id > 0
    && ['name', 'email', 'statusLabel', 'createdAt', 'linkedAccountLabel'].every(key => text(row[key]))
    && statuses.has(row.status)
    && typeof row.linkedAccount === 'boolean'
    && Array.isArray(row.actions) && row.actions.every(validAction);

export function validateSnapshot(value, raffleId) {
    if (!value || typeof value !== 'object' || value.version !== 1 || value.raffleId !== raffleId
        || !Array.isArray(value.rows) || !value.rows.every(validRow)) return null;
    const { counts, pagination, copy } = value;
    if (!counts || !['active', 'flagged', 'cancelled', 'total'].every(key => integer(counts[key]))
        || counts.active + counts.flagged + counts.cancelled !== counts.total) return null;
    if (!pagination || !integer(pagination.current) || pagination.current < 1
        || !integer(pagination.last) || pagination.last < pagination.current
        || pagination.perPage !== 25 || pagination.total !== counts.total
        || !url(pagination.canonicalUrl) || !Array.isArray(pagination.links)
        || !pagination.links.some(link => link.current && link.page === pagination.current)
        || !pagination.links.every(link => link && integer(link.page) && link.page > 0
            && link.page <= pagination.last && url(link.url) && typeof link.current === 'boolean')) return null;
    if (!url(value.loginUrl) || !copy
        || !['paginationLabel', 'page', 'navigationError', 'mutationError', 'reconciliationError', 'retryLabel', 'sessionExpired', 'loginLabel'].every(key => text(copy[key]))
        || !copy.summary || !['active', 'flagged', 'cancelled', 'total'].every(key => text(copy.summary[key]))) return null;
    return value;
}
