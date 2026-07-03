# Testing Raffles — SDD Workflow Gates

These rules are project-specific and override the generic SDD flow for this repository.

## Required SDD Order

For every new SDD slice in this repository, use this order:

```text
clean main → feature branch → exploration → approved issue → proposal → spec/design/tasks → apply → verify → archive → commit → push → PR
```

Do not treat branch creation or issue creation as PR-time cleanup. They are slice gates.

## Branch Gate

Before any SDD phase writes repository files for a new slice:

1. Verify the working tree is clean.
2. Verify the current branch is `main` aligned with `origin/main`, unless the user explicitly selected a different base.
3. Create a feature branch for the slice before writing OpenSpec artifacts, code, tests, or archive files.
4. Do not write project files for a new slice while still on `main`.

If work accidentally starts on `main`, stop immediately, run a read-only git audit, explain the incident, and ask for confirmation to create a feature branch preserving the working tree before continuing.

## Issue Gate

After `sdd-explore` succeeds and before `sdd-propose` starts:

1. Create a GitHub issue for the selected slice.
2. Add the required labels, including `status:approved`.
3. Persist the issue number and URL in Engram.
4. Record the issue reference in the OpenSpec context when using OpenSpec or hybrid artifact storage.
5. Do not run proposal, spec, design, tasks, apply, verify, or archive until the approved issue exists.

## PR Gate

Every PR must:

1. Link the approved issue with `Closes #<issue-number>` or equivalent GitHub closing keyword.
2. Include exactly one `type:*` label.
3. Be opened from the feature branch, not from `main`.

## Handoff Rule

Session handoffs belong in Engram by default. Only write handoffs to OpenSpec when they belong to an active SDD slice and will be committed in that slice's branch/PR.

## Final Handoff Gate

Do not generate the final session/slice handoff automatically.

When the slice is finished, the PR has been merged in GitHub, local `main` is updated, and no delivery tasks remain, remind the user that the final handoff is still pending and ask for explicit confirmation before generating it.

Use this handoff instruction only after the user confirms:

```text
Generá un handoff breve para continuar en una nueva sesión. Incluí:
- objetivo del cambio
- estado git final
- decisiones tomadas
- archivos tocados
- archivos pendientes o follow-ups
- comandos ejecutados
- riesgos conocidos
- próximo paso recomendado

Guardalo como resumen de sesión en Engram.

Solo escribí o actualizá archivos OpenSpec si el handoff pertenece a una slice SDD activa y va a quedar commiteado dentro del branch/PR correspondiente.

No modifiques archivos del repo solo para crear un handoff de sesión.

Antes de escribir archivos, commitear, pushear o abrir PR, pedime confirmación explícita.
```
