# Sprint 1 — Clickable MVP

Status: **In Progress** (Slice 1)

---

# Method

**Sprint** = delivery goal · **Slice** = implementation unit (2–6 h)

No monolithic sprint prompts. One slice → review → next slice.

---

# Slices

| Slice | Deliverable | Status |
| ----- | ----------- | ------ |
| **1** | App shell + navigation (`S1-SLICE-01`) | **Done** |
| **2** | UI Foundation — Design Tokens + components (`S1-SLICE-02`) | **Done** |
| **3A** | Dashboard composition (`S1-SLICE-03A`) | **Done** |
| **3B** | Dashboard mock data (`S1-SLICE-03B`) | **Done** |
| 4 | Library with mock list (`S1-SLICE-04`) | Next |
| 4 | Library with mock list | Planned |
| 5 | Import PDF simulated | Planned |
| 6 | Processing simulated | Planned |

---

# Slice 1 — App Shell

## Routes

```text
/           Dashboard
/import     Import
/library    Library
/settings   Settings
```

## Structure

```text
app/router.tsx
app/AppLayout.tsx
components/ui/Sidebar/
components/ui/Topbar/
components/ui/PageContainer/
pages/DashboardPage.tsx
pages/ImportPage.tsx
pages/LibraryPage.tsx
pages/SettingsPage.tsx
```

## Out of scope

* Backend API
* Mock business data
* Upload / AI

## Done when

- [x] Responsive layout (sidebar + topbar)
- [x] React Router navigation
- [x] Four pages render
- [x] `npm run check` / `test` / `build` pass

---

# Cursor prompt (Slice 1)

See CTO message — implement shell only.

---

# After Slice 1

Validate → Slice 2 (UI kit) → Slice 3 (Dashboard mock) …
