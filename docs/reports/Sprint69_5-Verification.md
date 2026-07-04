# Sprint 69.5 — Verification

**Task:** [TASK-0069.5](../../planning/Shadow/Sprint-69.5/TASK-0069.5.md)

## Manual checks

- [ ] Connect extension to Lumen (`/settings/shadow/browser`)
- [ ] YouTube overlay visible when connected
- [ ] **Explain** shows loading then explanation panel
- [ ] **Translate** opens language picker then shows translation
- [ ] **Summarize** shows key points
- [ ] **Save to Brain** shows success toast
- [ ] **Open Watch** on new video → import dialog → opens Watch tab
- [ ] **Open Watch** on imported video → opens Watch tab directly

## Automated

| Suite | Status |
| ----- | ------ |
| PHPUnit `ShadowBrowserControllerTest` (explain, open-watch) | Run via `make test-backend` |
| PHPUnit `BrowserYouTubeUrlParserTest` | Run via `make test-backend` |
| browser-extension Vitest | ✅ local |
| browser-extension build | ✅ local |
| Docker prod-like | Pending |

## Notes

- Open Watch uses `lumenWebBase` for frontend routes (`/video/{id}/watch`).
- Save to Brain uses `resourceType: youtube` on YouTube pages.
