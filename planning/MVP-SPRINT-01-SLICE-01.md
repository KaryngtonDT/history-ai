# S1-SLICE-01 — Application Shell

Status: **Done**

Sprint: **MVP Sprint 1 — Clickable MVP**

---

# Objective

Build the History AI application shell — navigable product structure with no business logic, API, or data.

---

# Deliverable

```text
+------------------------------------------------------+
| Sidebar                  | Topbar                    |
|--------------------------|---------------------------|
| Dashboard                |                           |
| Import                   |      Page Content         |
| Library                  |                           |
| Settings                 |                           |
+------------------------------------------------------+
```

---

# Routes

| URL | Page |
| --- | ---- |
| `/` | Dashboard |
| `/import` | Import |
| `/library` | Library |
| `/settings` | Settings |

---

# Structure

```text
frontend/src/
  app/
    router.tsx
    AppLayout.tsx
    providers/
  components/ui/
    Sidebar/
    Topbar/
    PageContainer/
  pages/
    Dashboard/DashboardPage.tsx
    Import/ImportPage.tsx
    Library/LibraryPage.tsx
    Settings/SettingsPage.tsx
```

---

# Out of scope

- Backend calls
- Mock business data
- Domain-specific components
- Upload / AI / business logic

---

# Definition of Done

- [x] React Router
- [x] Sidebar with active highlight
- [x] Topbar
- [x] Single reusable layout
- [x] Four pages with placeholder copy
- [x] `npm run build`
- [x] `npm test`
- [x] `npm run check`

---

# Next slice

**S1-SLICE-02 — UI Foundation** (Button, Card, Badge, Progress, EmptyState, …)
