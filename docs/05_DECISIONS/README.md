# Architecture Decisions

Version: 1.0

Status: Active

---

# Role

This folder records **approved architectural decisions** derived from validated RFCs.

| Artifact | Purpose | Lifecycle |
| -------- | ------- | --------- |
| [RFC](../06_RFC/README.md) | Proposal, debate, trade-offs | Draft → Review → **Accepted** or Rejected |
| ADR (here) | Frozen decision record | Accepted RFC → ADR → immutable unless superseded |

---

# Relationship to RFC

```text
Problem identified
        ↓
RFC written (docs/06_RFC/)
        ↓
Review (CTO + stakeholders)
        ↓
RFC Status: Accepted
        ↓
ADR created (docs/05_DECISIONS/)
        ↓
SYSTEM_BLUEPRINT / code updated
        ↓
Tasks generated from RFC
```

An RFC may produce **one or more ADRs**. Each ADR answers: *What did we decide, and why?*

---

# ADR Format

```text
ADR-XXXX-short-title.md

- Status: Proposed | Accepted | Superseded
- Date:
- RFC: RFC-XXXX (source)
- Context
- Decision
- Consequences
```

---

# Index

| ADR | Title | RFC | Status |
| --- | ----- | --- | ------ |
| — | *No ADRs yet. Pending RFC-0001 acceptance.* | — | — |

---

# Precedence

```text
Accepted RFC  →  ADR  →  SYSTEM_BLUEPRINT  →  Task  →  Code
```

When an ADR and the blueprint conflict, the **newer accepted ADR** wins until the blueprint is updated.
