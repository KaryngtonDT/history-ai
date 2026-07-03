# Shadow Browser Companion

Sprint 69 — Browser as a Shadow Client.

## Principle

> Learn where you are.

The browser extension contains **no intelligence**. All reasoning flows through Lumen Platform APIs and Shadow Presence.

## Stack

```text
Browser Extension (MV3)
        │
Browser Presence (Domain/ShadowBrowser)
        │
Shadow Presence (Sprint 68)
        │
Context Hub → Shadow Intelligence → Lumen Platform
```

## Bounded context

| Layer | Path |
| ----- | ---- |
| Domain | `App\Domain\ShadowBrowser\` |
| Application | `App\Application\ShadowBrowser\` |
| Infrastructure | `App\Infrastructure\ShadowBrowser\` |
| HTTP | `App\Presentation\Http\Controller\ShadowBrowser\` |

Persistence: `storage/shadow/browser/{workspaceId}.json`

## Settings UI

`/settings/shadow/browser` — `BrowserCenter`

## Extension

`browser-extension/` — load `dist/` unpacked in Chrome/Edge/Brave.
