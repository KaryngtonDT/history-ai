# Lumen Shadow Desktop

Tauri foundation for the Shadow Desktop Companion (Sprint 68).

## Scope

- Secure connection to Lumen backend (`/api/shadow/presence/*`)
- Local API base URL configuration
- Shadow profile sync via Context Hub
- Quick Launcher UI (search, mission resume, deep links)
- Window architecture stub via Tauri 2

## Out of scope (S68)

- Global OS shortcuts
- Clipboard or browser monitoring
- Full offline mode

## Development

```bash
cd desktop
npm install
npm run dev
```

Native shell (requires Rust, Tauri CLI, and on Windows MSVC Build Tools):

```bash
npm run tauri dev
```

On Windows, use `tauri-dev.cmd` from the repo root to load MSVC and Cargo before `tauri dev`:

```cmd
desktop\tauri-dev.cmd
```

Icons are generated from `app-icon.png` via `npm run tauri icon app-icon.png` if you need to regenerate them.

Environment:

- `VITE_API_BASE_URL` — default `http://localhost:8000`
- `VITE_LUMEN_WEB_BASE_URL` — default `http://localhost:5173`
