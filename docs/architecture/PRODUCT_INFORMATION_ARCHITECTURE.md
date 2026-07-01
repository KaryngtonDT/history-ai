# Product Information Architecture

Version: 1.0  
Date: 2026-07-01  
Status: Accepted

---

## Vision

**Lumen** (public product name) follows one product flow:

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
| **Feature pages** | `/video/:id/transcript`, `/video/:id/watch`, `/settings/pipeline`, `/settings/learning` | Focused work on one capability |
| **Settings** | `/settings`, `/settings/ai`, `/settings/pipeline`, `/settings/learning` | Configuration only |

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

## Sprint 54 — Compact layouts & workspace tabs

### Product name

- User-facing brand: **Lumen**
- Tagline: *AI video and knowledge localization platform.*
- Technical repo/DB identifiers remain `history-ai` until a dedicated migration sprint.

### Create page layout

| Route | Primary (left) | Secondary (right, collapsed by default) |
|-------|----------------|-------------------------------------------|
| `/video/upload` | Mode + dropzone | AI Director previews, help |
| `/audio/upload` | Mode + dropzone | Pipeline explanation, help |
| `/youtube/import` | URL + preview + import | What happens next, help |
| `/import` | PDF dropzone | Next steps, supported files, help |

Use `CompactPageIntroduction` + `CreatePageLayout` from `frontend/src/features/product/`.

### Workspace

`/workspace` uses **local tabs** (not new routes): Projects, Team, Analytics, History, Reviews, Preferences.

- Analytics/history/review data loads only when the tab is active.
- Projects tab includes a **sticky batch bar** (video count, languages, process CTA, progress).

### Empty states

Disabled sidebar items and empty panels must state what is missing, why it matters, and the next action (`EmptyState` + `shell.nav.empty.*` i18n keys).

---

## Sprint 57 — Adaptive Learning Center

Route: `/settings/learning`

| Section | Purpose |
| ------- | ------- |
| Learning profile | Signal/insight/recommendation counts and adaptive status |
| Signal timeline | Append-only usage signals with types and timestamps |
| Insights | Deterministic patterns with source signal references |
| Recommendations | Actionable suggestions with “generated because…” explanations |
| Adaptive toggle | Enable/disable adaptive help for Shadow and AI Director |
| Reset panel | Clear learning state; preferences preserved unless changed |

Settings hub (`/settings`) links to the Learning Center alongside AI Engines and Pipeline Configuration.

i18n keys live in `learning.*` (feature) and `settings.learning.*` (settings hub link) — never as a top-level `settings` object in feature locale files (shallow merge).

---

## Related docs

- [Sprint 54 Verification](../reports/Sprint54-Verification.md)

- [Sprint 50 PX Verification](../reports/Sprint50-PX-Verification.md)
- [Sprint 50.5 Verification](../reports/Sprint50_5-Verification.md)
- [Product UX Audit](../reports/ProductUXAudit.md)
