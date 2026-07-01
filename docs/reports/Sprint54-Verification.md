# Sprint 54 — Lumen Rebrand & Compact Product Layout

**Date:** 2026-06-26  
**Status:** Complete

---

## Product goal

Rename the user-visible product from **History AI** to **Lumen** and reduce excessive scrolling on key create and workspace pages — without risky backend or database renames.

**Tagline:** AI video and knowledge localization platform.

---

## Commits

| Slice | Message |
|-------|---------|
| 01 | `feat(brand): rename product to Lumen` |
| 02 | `feat(ux): compact create page layouts` |
| 03 | `feat(ux): add workspace tabs and sticky actions` |
| 04 | `feat(ux): improve empty states and disabled navigation` |
| 05 | `docs(ux): document Lumen product experience` |

---

## Why Lumen?

- **Lumen** signals clarity and illumination — making video and knowledge understandable across languages.
- The rebrand is **user-visible first**: app title, shell, settings, README, and in-app copy.
- Technical identifiers (`history-ai` repo, PostgreSQL DB name, PHP namespaces, historical report filenames) are **intentionally unchanged** to avoid migration risk before the public API (Sprint 55).

---

## User-visible rename

| Area | Change |
|------|--------|
| Browser title, favicon | Lumen |
| Sidebar brand + tagline | Lumen |
| Home / Mission Control | Lumen |
| Settings descriptions | Lumen |
| Pipeline / workspace i18n strings | History AI → Lumen where user-facing |
| README title | `# Lumen` |

## Intentionally not renamed

| Area | Reason |
|------|--------|
| Backend PHP namespaces / classes | No ROI; regression risk |
| Database tables / `history_ai` DB | Requires migration |
| Repository folder `HistoryAI` | Git history and tooling |
| Historical sprint report filenames | Audit trail |
| Mock transcript sample text | Fixture content, not branding |
| `package.json` name `history-ai-frontend` | npm / CI stability |

---

## Compact layout rules (create pages)

Components: `CompactPageIntroduction`, `CreatePageLayout`, `CollapsibleSection`.

| Route | Primary column (left) | Secondary column (right) |
|-------|----------------------|--------------------------|
| `/video/upload` | Processing mode, dropzone | AI previews (collapsed), help |
| `/audio/upload` | Processing mode, dropzone | Pipeline explanation, help |
| `/youtube/import` | URL, preview, import | What happens next, help |
| `/import` | PDF dropzone | What happens next, supported files, help |

Rules:

- Primary action visible without long scroll on desktop.
- Help and AI preview panels **collapsed by default** (`<details>` / `CollapsibleSection`).
- Two columns from `1024px`; single column on mobile.
- No fake controls (e.g. no target-language picker on upload without backend support).

---

## Workspace tab rules

Local tabs on `/workspace` (no new routes):

| Tab | Content |
|-----|---------|
| Projects | Video grid, artifact journey, sticky batch bar |
| Team | `TeamPanel` |
| Analytics | Dashboard, provider stats, performance, quality trend |
| History | `ExecutionHistoryPanel` |
| Reviews | `ReviewPanel`, review history |
| Preferences | `PreferenceProfileCard` |

- Heavy panels load only when their tab is active.
- **Sticky action bar** on Projects: video count, target languages, process button, batch progress.

---

## Empty state rules

Every empty or disabled surface explains:

1. What is missing  
2. Why it matters  
3. What to do next  
4. Action button or link where applicable  

Implemented via shared `EmptyState` and sidebar `shell.nav.empty.{id}` hints (`reason`, `why`, `action`).

---

## Validation

| Check | Result |
|-------|--------|
| `php bin/phpunit` | ✅ |
| `php bin/phpunit tests/Architecture` | ✅ |
| `php bin/phpunit tests/Functional/OpenApi` | ✅ |
| `npm run build` | ✅ |
| `npm test` | ✅ 649 tests |
| `npm run check` | ✅ |
| `pytest` (worker) | ✅ |
| `ruff check .` (worker) | ✅ |

---

## CTO checklist

- [x] User-visible product name is **Lumen**
- [x] No unsafe backend / domain / database rename
- [x] Create pages keep primary action above the fold
- [x] `/workspace` is tabbed and easier to scan
- [x] Heavy panels lazy-loaded per tab
- [x] Disabled navigation explains what to do
- [x] Empty states guide the user with actions
- [x] No fake routes or controls
- [x] No backend behavior regression
- [x] All suites green

---

## Roadmap

```text
54   Lumen UX            ← this sprint
55   Public API
```

---

## Known limitations

1. Internal repo, Docker compose project, and database still use `history-ai` identifiers.
2. Feature Academy long-form help body remains mostly English (headings localized since Sprint 53).
3. `PageIntroduction` (full variant) still used on pipeline step pages and workspace header — only create flows use `CompactPageIntroduction`.
4. Workspace history/reviews tabs require a project video; empty state directs users to Projects tab.
