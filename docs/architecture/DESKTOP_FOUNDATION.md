# Desktop Foundation — Architecture

Version: 1.0

Status: Planned (Sprint 68)

Parent: [SHADOW_PRESENCE.md](SHADOW_PRESENCE.md)

---

# Stack

- **Tauri 2** — native shell, small footprint
- **Frontend** — reuse Lumen design tokens where practical; minimal UI
- **Backend** — existing Lumen Symfony API (`/api/shadow/presence/*`)

---

# Design intent

The Desktop Companion is **not a copy of Lumen**.

It is ultra-light — Raycast / Spotlight / ChatGPT Desktop class:

| Capability | In desktop | In Lumen web |
| ---------- | ---------- | ------------ |
| Quick question | ✅ | ✅ |
| Second Brain search | ✅ | ✅ |
| Mission resume | ✅ | ✅ |
| Voice (future) | ✅ stub | ✅ |
| Full pipeline / workspace | ❌ deep link | ✅ |
| Complex settings | minimal | ✅ full |

Target: open in **< 300 ms**.

---

# Folder layout

```text
desktop/
  src-tauri/          # Rust shell, global shortcut
  src/
    ShadowDesktopApp/
    ShadowOverlay/    # floating panel
    QuickAssist/      # command palette UX
    ConversationBridge/
    ContextPanel/
  package.json
```

---

# MVP flows

1. **Quick Assist** — `Ctrl+Shift+Space` → ask / search / resume
2. **Concept open** — search result → context panel with Brain detail
3. **Continue in Lumen** — deep link to `/settings/shadow/brain` or watch page

---

# Out of scope (S68)

- System tray background monitoring
- OS-wide text selection hooks
- Offline mode
- Auto-update channel (manual build OK for MVP)
