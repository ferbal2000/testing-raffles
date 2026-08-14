# Exploration: reconcile-raffle-title-specs

## Exploration: Reconcile delivered raffle-title contracts

### Current State

The repository is clean on `chore/reconcile-raffle-title-specs` at `5e3185c12c3bc1cde313ee3885cce4f2b830a56a`, aligned with `origin/main`. The cancelled V1 stash remains untouched at commit `25c998200a6ad695de9830831e37680d2ed77aba`.

PRs #74, #75, and #76 already delivered title persistence, admin authoring, and title-first presentation. Current code and focused tests prove the required title, 100-character bound, trimming before validation, whitespace-only rejection, duplicate acceptance, editing in `draft`, `published`, and `closed`, escaped edit rendering, legacy `Sorteo #<ID>` backfill, defensive re-backfill, and database non-null enforcement. No product implementation gap was found.

The archived `admin-raffle-title-v2` change and current canonical presentation specs correctly cover title-plus-ID rendering for the admin list, admin participation list, public catalog, and public detail. However, V2 intentionally excluded persistence and authoring already delivered by PRs #74 and #75, so its archive does not preserve those contracts.

Three canonical specs still describe pre-title behavior:

| Canonical spec | Exact gap against delivered behavior and stash evidence |
|---|---|
| `openspec/specs/admin-raffle-create/spec.md` | Purpose still says availability-only; successful creation does not require or persist a normalized title, define the 100-character limit, reject invalid titles, or allow duplicates. |
| `openspec/specs/admin-raffle-edit/spec.md` | Purpose and form scenario still say availability-only; update still persists only availability and omits escaped title/ID display, normalized title editing, the 100-character limit, duplicate acceptance, invalid-update atomicity, and title editing across all current statuses. |
| `openspec/specs/raffle-lifecycle/spec.md` | Persisted record still omits the required bounded title, duplicate acceptance, legacy `Sorteo #<ID>` backfill before non-null enforcement, and editability of generated titles. |

The four unique stash blobs are therefore evidence, not restoration candidates. Their three delta specs contain the missing observable contracts. Their `design.md` uniquely records why rollout used nullable add, initial ID-chunked backfill, required admin authoring, defensive null-only re-backfill, and final non-null enforcement without a default.

The migration sequence is implemented exactly in:

- `database/migrations/2026_08_04_000000_add_nullable_title_to_raffles_table.php`
- `database/migrations/2026_08_04_000001_backfill_raffle_titles.php`
- `database/migrations/2026_08_04_000002_make_raffle_title_required.php`

The observable compatibility outcome belongs in the lifecycle delta: legacy titleless records receive editable `Sorteo #<ID>` values before non-null enforcement. The deployment mechanism and reason for the repeated backfill do **not** belong in a normative delta spec; they belong in this change's design and eventual archive as historical architecture rationale.

### Affected Areas

- `openspec/specs/admin-raffle-create/spec.md` — eventual canonical merge must add delivered title authoring behavior while preserving all availability and authentication scenarios.
- `openspec/specs/admin-raffle-edit/spec.md` — eventual canonical merge must replace availability-only form/update language with the full title-and-availability contract.
- `openspec/specs/raffle-lifecycle/spec.md` — eventual canonical merge must define required bounded title persistence and legacy backfill compatibility.
- `openspec/changes/reconcile-raffle-title-specs/specs/admin-raffle-create/spec.md` — future delta should modify the complete successful-create requirement.
- `openspec/changes/reconcile-raffle-title-specs/specs/admin-raffle-edit/spec.md` — future delta should modify the complete form and update requirements, including the accurate requirement rename.
- `openspec/changes/reconcile-raffle-title-specs/specs/raffle-lifecycle/spec.md` — future delta should modify the complete persisted-record requirement.
- `openspec/changes/reconcile-raffle-title-specs/design.md` — future design should preserve migration sequencing, defensive re-backfill rationale, rollback boundaries, and the absence of a permanent default.
- `openspec/changes/archive/2026-08-11-admin-raffle-title-v2/` — read-only evidence that presentation contracts are already archived and must not be duplicated or rewritten.
- Stash commit `25c998200a6ad695de9830831e37680d2ed77aba` — read-only source evidence; deletion remains a separate explicitly authorized Git operation after reconciliation acceptance.

### Approaches

1. **Focused three-spec reconciliation with design preservation** — create full `MODIFIED` deltas for create, edit, and lifecycle, then preserve migration rationale in the new design/archive.
   - Pros: Smallest complete scope; separates observable requirements from implementation rationale; preserves existing canonical scenarios; avoids touching delivered product code or already-correct presentation specs.
   - Cons: Requires careful full-block delta construction and semantic cross-checking against four stash blobs.
   - Effort: Low

2. **Restore or recreate the cancelled V1 change wholesale** — recover all V1 artifacts and reconcile them against V2.
   - Pros: Retains the original artifact set verbatim.
   - Cons: Reintroduces 22 delivered, obsolete, or superseded paths; duplicates V2 presentation history; expands review scope; risks stale requirements and accidental product changes.
   - Effort: High

### Recommendation

Use the focused three-spec reconciliation. Proposal scope should be documentation-only and explicitly exclude application code, tests, migrations, canonical presentation specs, and edits to archived artifacts.

Future deltas must preserve complete existing requirement blocks and add only delivered behavior:

- Create: required normalized title, maximum 100 characters, invalid-title rejection with old input, duplicate acceptance, and draft creation.
- Edit: escaped title and numeric ID on the form; normalized title plus availability updates; duplicate acceptance; invalid-update atomicity; editability in all three current statuses.
- Lifecycle: required bounded title persistence, duplicate acceptance, and editable `Sorteo #<ID>` compatibility backfill before non-null enforcement.

The future design should record the nullable-add → initial backfill → authoring → defensive re-backfill → non-null sequence, why the second null-only backfill was necessary during stacked delivery, preservation of authored titles, rollback behavior, and no permanent database default.

Acceptance evidence for a later stash-deletion decision should be a semantic checklist showing that all unique statements from the four stash blobs exist in either the merged canonical requirements or the archived reconciliation design. It should also confirm that the four already-correct presentation capabilities remain unchanged, no product files were modified, the reconciliation change is archived, and the stash commit still resolves unchanged. Dropping the stash must remain a separate, explicit user-authorized operation.

### Risks

- A partial `MODIFIED` requirement could erase valid authentication, availability, redirect, or lifecycle scenarios during archive merge.
- Copying migration mechanics into normative requirements would confuse product behavior with a completed rollout implementation.
- Editing the historical V2 archive would damage audit history; the new archive must supplement it instead.
- Treating test presence as permission to delete the stash would be insufficient; deletion requires semantic coverage of all four unique blobs and separate authorization.
- The issue gate is not yet satisfied for this named change, so proposal and later phases remain blocked.

### Ready for Proposal

No. Exploration is complete, but repository policy requires a new structured maintainer SDD issue to be created, reviewed, and moved from `status:needs-review` to `status:approved` before proposal. No GitHub resource should be created or modified during this phase.
