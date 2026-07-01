# Sprint 53 — Internationalization & Localization

**Date:** 2026-07-13  
**Status:** Complete

---

## Product goal

Users can use the full interface in **English**, **French**, or **German**.

---

## Commits

| Slice | Message |
|-------|---------|
| 01 | `feat(i18n): add localization foundation` |
| 02 | `feat(i18n): localize product shell` |
| 03 | `feat(i18n): localize ai pipeline features` |
| 04 | `feat(i18n): localize workspace and analytics` |
| 05 | `docs(i18n): document multilingual interface` |

---

## Architecture

```text
frontend/src/i18n/
├── I18nProvider.tsx      # React context
├── useTranslation.ts     # t(), locale, setLocale
├── i18n.ts               # translate() + fallback
├── languageStorage.ts    # localStorage + browser detection
├── locales/
│   ├── en.ts             # merges en + fr + de exports
│   └── sections/
│       ├── shell.{en,fr,de}.ts
│       ├── pipeline.{en,fr,de}.ts
│       └── workspace.{en,fr,de}.ts
└── scripts/localization-audit.mjs
```

- **No i18next** — lightweight typed dictionaries
- **No backend changes** — UI-only
- **Default:** `en`; **persistence:** `history-ai-locale`

---

## Language switcher

`Settings → Interface language` (`LanguageSettings` component)

Options: English / Français / Deutsch

---

## Validation

| Check | Result |
|-------|--------|
| PHPUnit (full suite) | ✅ |
| `npm run build` | ✅ |
| `npm test` | ✅ 649 tests |
| `npm run check` | ✅ |
| `pytest` + `ruff` (worker) | ✅ |
| Localization audit script | ✅ |

---

## CTO checklist

- [x] UI supports English, French, German
- [x] Language switcher visible and persistent
- [x] Product shell translated
- [x] Pipeline pages translated
- [x] Workspace / review / analytics translated
- [x] Help section labels translated
- [x] User-generated content not auto-translated
- [x] No backend business behavior changed
- [x] Route paths unchanged
- [x] Provider/model names unchanged
- [x] Repository/Service architecture preserved

---

## Roadmap

```text
53   i18n (EN/FR/DE)     ← this sprint
54   Public API
55   Official SDKs
```

---

## Known limitations

1. Feature Academy long-form help prose remains English (section headings localized).
2. Backend-generated recommendation/error text not localized.
3. Fourth language requires new section files + `SUPPORTED_LOCALES` update.

See also [Sprint53-LocalizationAudit.md](./Sprint53-LocalizationAudit.md).
