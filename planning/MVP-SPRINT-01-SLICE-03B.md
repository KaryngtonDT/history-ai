# S1-SLICE-03B — Dashboard Mock Data

Status: **Planned**

Depends on: **S1-SLICE-03A**

---

# Objective

Wire mock data into the dashboard composition.

---

# Mock data

```text
services/mock/
    contents.ts
    statistics.ts
```

---

# Changes

- `RecentContents` — render content list from mocks
- `Statistics` — render stats panel from mocks
- Add `RecentContentCard` sub-module if needed

`Dashboard.tsx` remains a pure orchestrator — no data logic.

---

# Constraints

- No backend, React Query, Zustand
