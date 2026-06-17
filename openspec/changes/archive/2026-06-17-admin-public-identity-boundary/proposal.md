# Proposal: Admin Public Identity Boundary

## Intent

Implement a real admin identity boundary without breaking the verified public contract: `App\Models\User` and `users` stay public-site identity, while admin auth becomes separate and isolated.

## Scope

### In Scope
- Add a separate admin identity model/table/guard/provider/broker boundary.
- Define admin/public session, remember-me, and route/middleware isolation.
- Drive the slice with strict TDD through `bin/test`.

### Out of Scope
- Renaming or migrating Laravel `users` / `User` away from the public website boundary unless a later proposal justifies it.
- Reverb, Echo, SSE, polling, or any real-time delivery before raffle/entry events exist.

## Capabilities

### New Capabilities
- `admin-identity-boundary`: Separate admin authentication, persistence, and session isolation from the public website identity.

### Modified Capabilities
- `platform-foundation`: Replace “admin identity deferred” with the implemented public/admin identity contract while preserving `users` as public.

## Approach

Follow the exploration recommendation: keep the slice bounded to identity separation. Add admin-specific auth primitives and guard tests, keep Blade-first delivery, and record real-time UI as a future cross-cutting constraint with Reverb/Echo as the likely default once raffle/entry/draw events exist.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `config/auth.php` | Modified | Add admin guard/provider/broker without redefining public `users`. |
| `config/session.php` | Modified | Define explicit cookie/session isolation expectations. |
| `bootstrap/app.php` | Modified | Enforce admin/public middleware boundary. |
| `app/Models/` + `database/migrations/` | New | Add admin identity model/table only. |
| `tests/Feature/Auth/*` | New/Modified | Add RED/GREEN coverage via `bin/test` for guard/session isolation. |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Archived identity wording leaks into implementation | Med | Lock proposal/specs to `users` = public, `admins` = admin. |
| Host separation is mistaken for full auth isolation | High | Require tests for guards, session cookies, brokers, and remember-me behavior. |
| Real-time scope creep breaks review budget | Med | Defer transport choices until domain events exist. |

## Rollback Plan

Revert admin-only auth/config/migration changes, remove new admin tests/spec deltas, and restore the foundation-only public identity contract.

## Dependencies

- Existing host-separated foundation and canonical `bin/test` runner.

## Success Criteria

- [ ] Proposal/spec work preserves `users` as the public identity boundary and introduces admin identity separately.
- [ ] Planned implementation is strict-TDD-first with `bin/test` proving admin/public guard and session isolation.
