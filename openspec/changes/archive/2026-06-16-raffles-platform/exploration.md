## Exploration: Raffles Platform

### Current State
The repository has no application code yet and currently contains only SDD bootstrap artifacts (`openspec/`, `.atl/`). There is no established stack, architecture, test runner, or deployment baseline. The requested product implies a greenfield platform with at least four core concerns: raffle lifecycle management, participant entry handling, controlled winner selection, and a trustworthy audit trail.

Likely core domain entities are: Raffle, Prize, Eligibility Rule, Entry, Participant, Payment/Receipt (if paid entry is allowed), Draw, Winner, Audit Event, Admin User, and Notification. Likely core workflows are: create/publish raffle, enroll or purchase participation, validate eligibility, close raffle, execute winner draw, record evidence, notify winners, and expose public/admin views of status.

Non-functional concerns inferred from the request are strong auditability, controlled execution, fraud prevention, operational traceability, and the ability to scale write-heavy entry traffic more than admin traffic.

### Affected Areas
- `openspec/config.yaml` — currently defines a greenfield project with no stack or testing baseline, so the first implementation change must establish foundational architecture explicitly.
- `openspec/specs/` — no source-of-truth domain specs exist yet; bounded contexts and MVP requirements will need to be introduced here in later SDD phases.
- `openspec/changes/raffles-platform/exploration.md` — this exploration artifact defines the initial system shape, MVP boundary, and architectural decision points.

### Approaches
1. **Modular monolith first** — One deployable application with clear internal modules for raffle management, participation, draw control, audit, and admin operations.
   - Pros: fastest path to MVP, simplest consistency model for entries and draws, easier audit trail design, lower ops overhead, easier to test and reason about in an empty repo.
   - Cons: later extraction is needed if one domain becomes a throughput bottleneck, requires discipline to keep modules isolated.
   - Effort: Medium

2. **Service-oriented split from day one** — Separate services for public participation, raffle administration, draw execution, and audit/event processing.
   - Pros: clearer scaling boundaries, stronger operational isolation for sensitive draw execution, easier independent deployment later.
   - Cons: high upfront complexity, distributed consistency and observability costs, slower MVP delivery, too much ceremony for an unvalidated greenfield product.
   - Effort: High

### Recommendation
Start with a **modular monolith backed by a relational database** and an explicit append-only audit model. The first change boundary should be **Raffle Lifecycle + Controlled Draw MVP**: admins can create and publish a raffle, participants can submit entries, the system can close a raffle, an authorized actor can trigger a draw exactly once, and the result is stored with immutable audit evidence.

Suggested bounded contexts for later specs are: **Raffle Catalog**, **Participation/Entries**, **Draw Control**, **Identity & Authorization**, **Audit & Compliance**, and **Notifications**. The MVP should only include the minimum slice across those contexts: raffle definition, public entry submission, eligibility checks, draw execution guardrails, winner record, and audit event history.

Key control points should include: role-based admin permissions, raffle state machine transitions, entry idempotency, draw locking/idempotency, eligibility validation, clock-based close rules, and immutable audit records for every sensitive action. For scalability, prioritize database constraints, transaction boundaries, indexed entry lookups, queue-backed asynchronous notifications, and a design that treats draw execution as low-frequency but high-integrity work while entry ingestion may become bursty.

A justified stack direction is: **single codebase, modular architecture, relational database first, background jobs for asynchronous work**. Framework choice is still a decision point because there is no team/context signal yet. If the team optimizes for JavaScript/TypeScript full-stack speed, a TypeScript stack is a good fit; if the team wants stronger built-in admin capabilities, a batteries-included backend framework is also viable. The database and audit model are more important than the web framework choice at this stage.

Open questions before proposal/spec depth increases: are entries free or paid, what eligibility rules are required, must the public verify draw fairness, are there jurisdiction/compliance requirements, what notification channels are needed, and what operational actors are allowed to trigger or approve a draw.

### Risks
- Auditability can be undermined if draw execution and state transitions are implemented before defining immutable event records and authorization boundaries.
- Scalability can be misdesigned if paid-entry, fraud, or compliance requirements emerge after the MVP data model is fixed.
- Public trust may require stronger fairness evidence (seed source, reproducibility, or approval workflow) than a naive random winner selection implementation provides.

### Ready for Proposal
Yes — the orchestrator should tell the user that the project is ready to propose a first change centered on a modular-monolith MVP for raffle creation, participant entry, controlled winner draw, and audit history, while capturing unresolved policy questions as proposal assumptions.
