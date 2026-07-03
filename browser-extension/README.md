# HistoryAI Shadow Browser Extension

Manifest V3 scaffold for Sprint 69 — connects to the Lumen backend Shadow Browser API and injects a floating Shadow panel on web pages.

## Prerequisites

- Node.js 20+
- Lumen backend running (default `http://localhost:8000`)

## Setup

```bash
cd browser-extension
npm install
npm run build
```

## Development

```bash
npm run dev    # watch build to dist/
npm test       # vitest platform detection tests
```

## Load unpacked (Chrome / Edge / Brave)

1. Run `npm run build`.
2. Open `chrome://extensions` (or `edge://extensions`).
3. Enable **Developer mode**.
4. Click **Load unpacked** and select the `browser-extension/dist` folder.
5. Pin the extension and open the popup to connect to Lumen.

## Options

Right-click the extension icon → **Options**, or open the options page from `chrome://extensions`. Set the **Lumen API base URL** (default `http://localhost:8000`).

## Firefox

Add to `manifest.json` under `browser_specific_settings`:

```json
"browser_specific_settings": {
  "gecko": {
    "id": "history-ai@lumen.local"
  }
}
```

Then load temporarily via `about:debugging` → **This Firefox** → **Load Temporary Add-on**.

## Structure

| Path | Purpose |
|------|---------|
| `background/service-worker.ts` | Connect on install, message router |
| `content/detector.ts` | Platform detection, sync to background |
| `content/overlay.ts` | Floating Shadow panel UI |
| `popup/` | Connection status and controls |
| `options/` | API base URL configuration |
| `shared/platforms.ts` | URL platform detection (mirrors backend) |
| `shared/api.ts` | Lumen `/api/shadow/browser/*` client |

## Shadow panel actions

- **Explain** — queue explain for current page
- **Translate** — queue translate
- **Summarize** — queue summarize
- **Save to Brain** — save page context
- **Open Watch** — YouTube only
