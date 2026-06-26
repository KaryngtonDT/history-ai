# S1-SLICE-03B — Dashboard Mock Data

Status: **Done**

---

# Data flow

```text
Dashboard
    ↓
DashboardService.getDashboard()
    ↓
DashboardRepository (interface)
    ↓
MockDashboardRepository
    ↓
mock/dashboard.ts
```

Components never import `mock/` directly.

---

# Files

```text
mock/dashboard.ts
services/dashboard/
    types.ts
    DashboardRepository.ts
    MockDashboardRepository.ts
    DashboardService.ts
```

---

# Next

**S1-SLICE-04** — Library with mock list
