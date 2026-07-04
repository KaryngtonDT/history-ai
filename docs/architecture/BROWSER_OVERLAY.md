# Browser Overlay

Content script injects a floating Shadow panel on allowed sites.

## Actions

Each button calls a real backend action (Sprint 69.5). See [BROWSER_ACTIONS.md](./BROWSER_ACTIONS.md).

- Explain — result panel
- Translate — language picker + result panel
- Summarize — key points panel
- Save to Second Brain — toast
- Open Watch (YouTube) — import dialog if needed, then opens `/video/{id}/watch`

## UX

- Discreet, collapsible, draggable stub
- Never auto-reads page content without explicit user action
- Permissions enforced per host via Browser Permission Center

Implementation: `browser-extension/content/overlay.ts`
