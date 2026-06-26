# Design Workspace

Version: 1.0

Status: Active

---

# Role

This folder holds **design artifacts** — not application code.

Everything here is created **before** frontend implementation.

Spec: [docs/07_DESIGN_SYSTEM/](../docs/07_DESIGN_SYSTEM/README.md)

---

# Structure

```text
design/
├── README.md           ← this file
├── brand/              ← logo, wordmark, brand guidelines
├── tokens/             ← colors.json, spacing.json (export to CSS)
├── wireframes/         ← low-fidelity MVP screens
├── mockups/            ← high-fidelity validated designs
├── components/         ← per-component design specs
└── icons/              ← custom icons (if any beyond Lucide)
```

---

# Workflow

```text
1. Product Design System docs (docs/07_DESIGN_SYSTEM/)
2. Wireframes (design/wireframes/)
3. Mockups + token JSON (design/mockups/, design/tokens/)
4. Design review / validation
5. Storybook + frontend implementation
```

---

# MVP Screens to Wireframe

| Screen | File (suggested) | Question |
| ------ | ---------------- | -------- |
| Dashboard | `wireframes/dashboard.md` | What can I learn today? |
| Library | `wireframes/library.md` | What have I imported? |
| Content Details | `wireframes/content-details.md` | Where is processing? |
| Upload | `wireframes/upload.md` | How do I import? |
| Settings | `wireframes/settings.md` | How do I configure? |

---

# Rules

* No code in `design/` — markdown, PNG, Figma exports, JSON tokens only
* Validated mockups are the source of truth for Phase A implementation
* Backend remains frozen until Feature-0001 (Phase B)
