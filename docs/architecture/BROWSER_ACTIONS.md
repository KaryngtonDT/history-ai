# Browser Actions

Sprint 69.5 connects overlay buttons to backend handlers via a single dispatcher.

## Flow

```text
Overlay (content script)
  → SHADOW_ACTION message
  → Service worker
  → POST /api/shadow/browser/{action}
  → BrowserActionDispatcher
  → Response → overlay result / toast / tab open
```

## Actions

| Action | Endpoint | User feedback |
| ------ | -------- | ------------- |
| Explain | `POST /api/shadow/browser/explain` | Result panel with summary + concepts |
| Translate | `POST /api/shadow/browser/translate` | Language picker → result panel |
| Summarize | `POST /api/shadow/browser/summarize` | Result panel with key points |
| Save to Brain | `POST /api/shadow/browser/save` | Success toast |
| Open Watch | `POST /api/shadow/browser/open-watch` | Import dialog if needed → new tab `/video/{id}/watch` |

## Payload

All POST actions accept page context in the JSON body:

```json
{
  "scopeKey": "default",
  "url": "https://www.youtube.com/watch?v=…",
  "title": "Page title",
  "platform": "youtube",
  "host": "youtube.com",
  "language": "fr",
  "importConfirmed": false
}
```

## Backend

- `App\Application\ShadowBrowser\BrowserActionDispatcher`
- `App\Application\ShadowBrowser\Handlers\PostBrowserActionHandler`
- `App\Presentation\Http\Controller\ShadowBrowser\ShadowBrowserController`

## Extension

- `browser-extension/shared/api.ts` — HTTP client
- `browser-extension/background/service-worker.ts` — dispatch + tab open
- `browser-extension/content/overlay.ts` — UI feedback
