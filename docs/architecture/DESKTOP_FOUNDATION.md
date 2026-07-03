# Desktop Foundation — Architecture

Version: 1.1

Status: Planned (Sprint 68)

Parent: [SHADOW_EVERYWHERE.md](SHADOW_EVERYWHERE.md)

---

# Sprint 68 split

| Slice | Deliverable |
| ----- | ----------- |
| P68-SLICE-03 | Tauri **foundation** — auth, profile sync, window architecture |
| P68-SLICE-07 | **Quick Launcher** — minimal UX on top of foundation |

Browser, IDE, and Mobile are **not** built in S68.

---

# Stack

- **Tauri 2** — native shell
- **Minimal UI** — launcher class, not Lumen clone
- **Backend** — `/api/shadow/presence/*` + existing Shadow APIs

---

# SLICE-03 — Foundation only

```text
desktop/
  src-tauri/           # Rust: window management, deep links
  src/
    app/
    auth/              # token / session to Lumen backend
    profile/           # Shadow identity sync
    windows/           # main + overlay layout
```

Goals:

- secure backend connection
- local authentication
- profile synchronization
- fast-open architecture (< 300 ms target for SLICE-07)
- window structure for future overlay

**Not in SLICE-03:** global shortcuts, clipboard, tray monitoring.

---

# SLICE-07 — Quick Launcher

Minimal commands:

- open Shadow
- search Second Brain
- open concept
- resume mission / conversation
- deep link to Lumen page

**Not in SLICE-07:** clipboard read, browser analysis, auto context.

---

# Lumen vs Desktop

| Capability | Desktop S68 | Lumen web |
| ---------- | ----------- | --------- |
| Quick search / ask | ✅ | ✅ |
| Full Second Brain UI | deep link | ✅ |
| Pipeline / workspace | deep link | ✅ |
| Complex settings | minimal | ✅ full |

---

# Future (S69+)

Desktop foundation enables later: global shortcut, tray presence, deep OS integration — each as **separate slices** with explicit privacy review.
