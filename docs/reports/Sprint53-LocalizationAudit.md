# Sprint 53 — Localization Audit

**Date:** 2026-07-13  
**Scope:** Frontend UI string coverage after i18n rollout

---

## Method

Automated scan via `frontend/scripts/localization-audit.mjs`:

- Walks `frontend/src/**/*.tsx` and `*.ts` (excluding tests, mocks, locale files)
- Flags JSX inline text that looks like user-visible English
- Allowlists technical identifiers (providers, routes, API paths, brand names)

Run:

```bash
node frontend/scripts/localization-audit.mjs
```

---

## Coverage by area

| Area | Status |
|------|--------|
| Product shell (sidebar, breadcrumbs, command palette) | ✅ Localized |
| Home / Mission Control | ✅ Localized |
| Settings + language selector | ✅ Localized |
| Video / audio / YouTube pipeline | ✅ Localized |
| Transcript, translation, TTS, voice, lip sync, render | ✅ Localized |
| Quality, intelligence, optimization, scheduler | ✅ Localized |
| Workspace, batch, team, roles | ✅ Localized |
| History, reviews, analytics | ✅ Localized |
| Library, collections, import, processing | ✅ Localized |
| Feature Academy **content** (long-form help prose) | ⚠️ English only (by design for slice 53 — section labels localized) |

---

## Intentionally not translated

- User-generated content (transcripts, translations, titles, project names)
- Provider/model names: FasterWhisper, Ollama, F5-TTS, OpenVoice, LatentSync, FFmpeg
- Route paths and API identifiers
- Backend enum payload values (`owner`, `editor`, etc.) — display labels only are translated
- AI Director recommendation text from backend (dynamic content)

---

## Known remaining English

1. **Feature help body text** in `features/help/content/features.ts` — product copy; section headings are localized via `help.academy.sections.*`.
2. **Dynamic backend strings** — orchestrator explanations, quality recommendations, error messages from API.
3. **Mock repository sample data** — test/demo titles like "The Roman Empire".
4. **Knowledge / chat / agent surfaces** (library graph, semantic search, agent mode, PDF flashcards) — ~40 strings flagged by audit script; lower-traffic paths deferred post-V1.

---

## Adding a new language

1. Copy `locales/sections/shell.en.ts` pattern for new section if needed.
2. Add locale code to `SUPPORTED_LOCALES` in `i18n/types.ts`.
3. Add `language.xx` label in all locale files.
4. Provide `xx` translations for each section file (`shell`, `pipeline`, `workspace`).
5. Merge into `locales/en.ts` export object.
6. Add tests in `i18n/*.test.tsx`.

---

## Key conventions

- Keys: `area.component.field` (e.g. `pipeline.upload.title`)
- Interpolation: `{{name}}` placeholders
- Fallback: missing keys fall back to English (`en`)
- Persistence: `localStorage` key `history-ai-locale`
