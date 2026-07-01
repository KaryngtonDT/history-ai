# Sprint 50 — Product Experience (PX) Verification

**Date:** 2026-06-26  
**Scope:** Platform Sprint 50 — Guided Product Experience & Product Coherence

---

## Summary

Sprint 50 transforms History AI from a collection of features into a **self-explanatory product surface**. No new backend capabilities were added. All changes are frontend-only, wiring existing Sprints 31–49 into a coherent navigation, help, artifact journey, and command palette.

---

## Changed Routes

| Route | Change |
|-------|--------|
| All routes | Wrapped in `ProductShell` (sidebar, breadcrumbs, command palette) |
| `/settings` | Replaced stub with hub linking to AI Engines and Pipeline Configuration |
| `/video/:id/*` | Added `PageIntroduction`, `ExplainThisButton`, `ArtifactJourney` |
| `/video/upload` | Added page introduction; upload success shows pipeline links + journey |
| `/workspace` | Added page introduction, artifact journey for selected video |
| `/settings/ai`, `/settings/pipeline` | Added page introductions and help |

No routes were added or removed.

---

## New Components

### Product shell (`frontend/src/features/product/`)

- `ProductShell` — app layout with sidebar, breadcrumbs, outlet
- `AppSidebar` — grouped navigation (Create, AI Pipeline, Results, Library, Settings)
- `ProductBreadcrumbs` — contextual path
- `PageIntroduction` — title, description, "What can I do here?"
- `VideoPipelinePageLayout` — shared header for video pipeline pages
- `ProductContext` — exposes `videoId` from URL for contextual nav
- `navigation.ts`, `videoRoutes.ts` — nav groups and pipeline step definitions

### Help system (`frontend/src/features/help/`)

- `HelpTooltip`, `FeatureAcademy`, `ExplainThisButton`
- `content/features.ts` — static help for 20 product features

### Artifact journey (`frontend/src/features/artifacts/`)

- `ArtifactJourney` — visual pipeline from Video → Quality
- `journeyModel.ts` — `buildArtifactJourney()` step builder

### Guidance (`frontend/src/features/guidance/`)

- `CommandPalette` — Ctrl+K search and navigation
- `commandItems.ts` — searchable command registry

---

## Feature Coverage Checklist

| Feature | Sprint | Reachable | Help | Notes |
|---------|--------|-----------|------|-------|
| Video upload | 31 | ✅ | ✅ | Sidebar, command palette, upload page intro |
| Transcript | 32 | ✅ | ✅ | Sidebar (contextual), pipeline pages |
| Translations | 33 | ✅ | ✅ | |
| Audio generation | 35 | ✅ | ✅ | |
| Voice clone | 36 | ✅ | ✅ | |
| Lip sync | 37 | ✅ | ✅ | |
| Final render | 38 | ✅ | ✅ | |
| Quality report | 40 | ✅ | ✅ | On upload page (automatic mode) |
| Pipeline configuration | 39 | ✅ | ✅ | Settings hub + sidebar |
| AI engine settings | 34 | ✅ | ✅ | Settings hub + sidebar |
| Automatic mode | 41 | ✅ | ✅ | Upload page processing mode selector |
| Video intelligence | 42 | ✅ | ✅ | Upload automatic preview |
| Execution optimization | 43 | ✅ | ✅ | Upload automatic preview |
| Scheduler / resources | 44 | ✅ | ✅ | Upload automatic preview |
| Workspace / batch | 45 | ✅ | ✅ | Sidebar, workspace page |
| Reviews / preferences | 46–47 | ✅ | ✅ | Workspace page panels |
| Collaboration / team | 48 | ✅ | ✅ | Workspace TeamPanel |
| Telemetry / analytics | 49 | ✅ | ✅ | Workspace analytics dashboard |
| History / reprocess | — | ✅ | ✅ | Workspace ExecutionHistoryPanel |
| Library / collections | — | ✅ | — | Sidebar Library group |

---

## Artifact Accessibility Checklist

| Artifact | Clickable | Journey card | Dependency hint |
|----------|-----------|--------------|-------------------|
| Video | ✅ Upload link | ✅ | — |
| Transcript | ✅ | ✅ | — |
| Translation | ✅ | ✅ | Transcript |
| Audio | ✅ | ✅ | Translations |
| Voice clone | ✅ | ✅ | Audio |
| Lip sync | ✅ | ✅ | Voice clone |
| Final render | ✅ | ✅ | Lip sync |
| Quality | ✅ Upload page | ✅ | Final render |

Integrated in: upload success, workspace video grid, all video pipeline pages.

---

## Help Coverage Checklist

All 20 features in `FEATURE_HELP` have: short explanation, details, best practice, common mistake, next step, FAQ (where applicable).

Pages with `ExplainThisButton`: upload, workspace, all pipeline pages, settings hub, AI engines, pipeline configuration.

---

## Onboarding Checklist

| Mechanism | Status |
|-----------|--------|
| Page introductions ("What can I do here?") | ✅ All major pages |
| Sidebar grouped navigation | ✅ |
| Contextual disabled hints (no video) | ✅ Results group |
| Command palette (Ctrl+K) | ✅ |
| Artifact journey map | ✅ |
| Feature academy drawer | ✅ |

Interactive first-run tour: deferred (command palette + page intros cover discoverability for v1).

---

## Accessibility

- Command palette: backdrop button, dialog role, Escape to close
- Sidebar: `aria-label`, disabled state titles
- Artifact journey: `aria-label` on section
- Page introductions: semantic `<header>` and `<h1>`
- Video grid pipeline links: descriptive link text

---

## Before / After UX Summary

**Before:** Video pipeline routes existed but were undiscoverable. Settings sidebar pointed to a stub. Upload success had no next steps. Workspace videos had no pipeline links.

**After:** Every major capability is reachable from the sidebar or Ctrl+K. Each page explains itself. The artifact journey shows dependencies and next actions. Upload and workspace surfaces link directly into the pipeline.

---

## Known Limitations

1. Artifact journey shows all steps as "open" when `videoId` is present — real artifact status from backend not yet wired (no status API in scope).
2. Quality artifact links to upload page (quality dashboard lives there in automatic mode preview).
3. Command palette searches static commands only — no live video/project search yet.
4. Provider inspector and recommendation explanation panels deferred; help academy covers provider context in text.
5. `VideoUploadHeader` removed from panel to avoid duplicating `PageIntroduction` on upload page.

---

## Validation Results

| Check | Result |
|-------|--------|
| `npm run build` | ✅ |
| `npm test` (621 tests) | ✅ |
| `npm run check` (Biome) | ✅ |
| `php bin/phpunit` | (see CI) |
| `php bin/phpunit tests/Architecture` | (see CI) |
| `php bin/phpunit tests/Functional/OpenApi` | (see CI) |
| `pytest` | (see CI) |
| `ruff check .` | (see CI) |

---

## Architecture Decisions

1. **No backend changes** — PX sprint is frontend-only.
2. **Reuse existing routes** — no invented paths.
3. **Repository/Service pattern preserved** — no direct fetch in new features.
4. **Static help metadata** — no help API; content in `features/help/content/`.
5. **Direct imports for `videoRoutes`** — avoids circular dependency with artifacts barrel.
6. **`journeyModel.ts`** — renamed from `artifactJourney.ts` to avoid Windows casing conflict with `ArtifactJourney/` folder.
