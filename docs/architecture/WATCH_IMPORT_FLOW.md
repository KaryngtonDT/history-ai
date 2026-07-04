# Watch Import Flow (Browser)

YouTube **Open Watch** from the browser extension.

## Steps

1. User clicks **Open Watch** on a YouTube page.
2. Extension calls `POST /api/shadow/browser/open-watch` with page context.
3. Backend checks whether the URL matches an existing imported video.
4. If found → response includes `watchPath` → extension opens `{lumenWebBase}/video/{id}/watch`.
5. If not found → `status: confirmation_required` → overlay shows import dialog.
6. User confirms → second call with `importConfirmed: true` → `ImportYouTubeHandler` runs → tab opens.

## Response statuses

| Status | Meaning |
| ------ | ------- |
| `completed` | Video already imported; Watch ready |
| `confirmation_required` | User must confirm import |
| `processing` | Import queued; Watch tab still opens |
| `unavailable` | Non-YouTube or invalid URL |
| `error` | Import failed |

## Configuration

Extension uses `lumenWebBase` from sync storage (default `http://localhost:5173`).
