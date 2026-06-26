# Component Catalog

Phase A delivers generic components only. Domain components live under `features/` in later Phases.

---

# Design Tokens

Location: `frontend/src/styles/tokens.css`

| Token group | Examples |
| ----------- | -------- |
| Color | primary, secondary, danger, surface, text, border |
| Spacing | xs, sm, md, lg, xl |
| Typography | font-family, sizes, weights |
| Radius | sm, md, lg |
| Shadow | sm, md |

---

# Generic Components

Location: `frontend/src/components/`

| Component | Purpose |
| --------- | ------- |
| Button | Actions — variants: primary, secondary, danger, disabled |
| Input | Text input |
| Card | Content container |
| Modal | Overlay dialog |
| Dialog | Accessible dialog wrapper |
| Dropzone | File selection — states: empty, hover, uploading, error, completed |
| Progress | Linear progress indicator |
| Badge | Small label |
| StatusChip | Processing status display |
| Spinner | Loading indicator |
| Toast | Transient notifications |
| EmptyState | No data placeholder |
| ErrorState | Error placeholder |
| PageHeader | Page title + actions |
| Section | Content section wrapper |

Each component: `ComponentName/ComponentName.tsx`, `index.ts`, `ComponentName.stories.tsx`, `ComponentName.test.tsx`.

---

# Layouts

Location: `frontend/src/layouts/`

| Layout | Use |
| ------ | --- |
| DashboardLayout | Main app — sidebar or top nav |
| CenteredLayout | Auth, focused flows |
| AuthLayout | Login/register (shell only) |

---

# Page Shells

Location: `frontend/src/pages/`

| Page | Route | Phase A behavior |
| ---- | ----- | ---------------- |
| Dashboard | `/` | Import buttons + empty state |
| Library | `/library` | Empty state shell |
| ContentDetails | `/content/:id` | Placeholder shell |
| Upload | `/upload` | Dropzone shell (no API) |
| Settings | `/settings` | Placeholder shell |

---

# Feature Modules (structure)

Location: `frontend/src/features/`

Create folder structure only in Phase A:

```text
features/
├── content/
├── upload/
├── processing/
├── library/
└── artifacts/
```

No business logic until vertical Feature slices (Phase B+).

---

# Storybook

Location: `frontend/.storybook/`

Script: `npm run storybook`

Every generic component must have stories covering all visual states.

---

# Separation Rule

| Layer | Knows about domain? |
| ----- | ------------------- |
| `components/` | No |
| `layouts/` | No |
| `pages/` | Shell only — labels, no API |
| `features/` | Yes — starting Phase B |
