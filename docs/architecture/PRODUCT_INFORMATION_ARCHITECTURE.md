# Product Information Architecture

Version: 1.0  
Date: 2026-07-01  
Status: Accepted

---

## Vision

History AI follows one product flow:

```text
Knowledge In → AI Processing → Knowledge / Media Out
```

Users bring **any source** (video, PDF, audio, YouTube). The pipeline adapts. The UI must teach this without external documentation.

---

## Page roles

| Layer | Route examples | Role |
|-------|----------------|------|
| **Home** | `/` | Orientation, create, resume work, recent work summaries |
| **Workspace** | `/workspace` | Projects, batch, team, detailed analytics |
| **WorkItem Overview** | `/video/:id`, `/processing/:id` | Complete life of one item — hub, not detail work |
| **Feature pages** | `/video/:id/transcript`, `/settings/pipeline` | Focused work on one capability |
| **Settings** | `/settings`, `/settings/ai` | Configuration only |

### Rules

1. **Home summarizes** — never embed full Workspace, Analytics, or Pipeline Builder panels.
2. **Overview links** — item hubs link to feature pages; they do not duplicate them.
3. **Feature pages work** — detail views contain panels and actions, not navigation-only stubs.
4. **No fake features** — if backend does not support an action, show explanation or omit.

---

## WorkItem read model

Product-level abstraction in `frontend/src/services/workItem/`:

```text
WorkItem
  ├── video      → /video/:id
  ├── pdf        → /processing/:id
  ├── audio      → /processing/:id
  ├── youtube    → /library
  └── project    → /workspace
```

**WorkItem is not a backend domain merge.** It aggregates existing `Content`, `Video`, and `Project` data for Home and navigation.

Fields: `id`, `type`, `title`, `status`, `progress`, `currentStep`, `openRoute`, `primaryActionLabel`, `capabilities[]`.

---

## Video hub

`/video/:videoId` is the **central hub** for a video:

- Pipeline tabs link to existing step routes
- Compact ArtifactJourney
- AI Director and Quality summaries (not full panels)
- Primary next action → transcript or current step

Home, Workspace, and Recent Work link videos to `/video/:id` first.

---

## Avoiding URL conflicts

| Risk | Mitigation |
|------|------------|
| Home duplicates Workspace | Home shows counts + links; Workspace keeps full panels |
| Overview duplicates feature pages | Overview = links + summaries only |
| Two homes | Single route `/` renamed conceptually to Home |
| Content vs Video confusion | WorkItem `type` + icon on every card |

---

## Backend domains (unchanged)

- `Content` — PDF/audio/legacy ingestion (Sprint 1–2)
- `Video` — localization pipeline (Sprints 31–49)
- `Project` — workspace batch (Sprint 45+)

Domains are **not merged** in this sprint. WorkItem is the UX unification layer.

---

## Future public API impact

Recommended API resources (Sprint 51+):

- `GET /api/work-items` — product read model
- `GET /api/videos/{id}` — video hub metadata
- Existing `/api/contents`, video endpoints unchanged

Public API should expose **WorkItem** concepts matching the UI, not raw internal splits.

---

## Related docs

- [Sprint 50 PX Verification](../reports/Sprint50-PX-Verification.md)
- [Sprint 50.5 Verification](../reports/Sprint50_5-Verification.md)
- [Product UX Audit](../reports/ProductUXAudit.md)
