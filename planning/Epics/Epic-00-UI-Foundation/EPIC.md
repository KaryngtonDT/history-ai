# Epic 00 — UI Foundation

Version: 1.0

Status: Active

Milestone: 2 — Product Experience

Phase: **A** (before Feature-0001)

---

# Objective

Build a reusable Design System and application shell so every future Feature ships with consistent UI — without accumulating component debt.

---

# Principle

```text
Generic components     →  know nothing about History AI
Feature components     →  compose generic components for domain
```

Example:

- `Button` — generic
- `UploadPdfButton` — domain (Feature-0001, later)

---

# Scope

| Deliverable | Location |
| ----------- | -------- |
| Design tokens | `frontend/src/styles/tokens.css` |
| UI components | `frontend/src/components/` |
| Layouts | `frontend/src/layouts/` |
| Page shells | `frontend/src/pages/` |
| Storybook | `frontend/.storybook/` |
| Feature modules | `frontend/src/features/` (structure only; logic in Features B+) |

---

# Out of Scope (Phase A)

* Backend API integration
* Upload logic
* Authentication implementation
* Real Library data

Shell UI only — static or placeholder state.

---

# Alignment

| Document | Relevance |
| -------- | --------- |
| [PRODUCT_MANIFESTO](../../../docs/00_PROJECT/PRODUCT_MANIFESTO.md) | Learning-first experience |
| [Engineering Constitution](../../../engineering/00_ENGINEERING_PRINCIPLES.md) | Simplicity, testability |
| Feature-0001 contract | Upload PDF will consume Dropzone, Progress, StatusChip |

---

# Definition of Done (Epic)

Epic complete when Feature-UI-0001 passes its contract and Dashboard shell is navigable in Storybook and the app.

---

# Next Epic

[Epic-01 — Content Ingestion](../Epic-01-Content-Ingestion/EPIC.md) — Feature-0001 PDF Upload (Phase B).
