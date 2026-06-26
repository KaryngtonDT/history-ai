# S2-SLICE-05 — Wire Dashboard to Real Backend

Status: **Done**

Epic: **Epic 02 — Real Content Flow**

---

# Goal

Dashboard reflects the same PostgreSQL data as Library via `GET /api/contents`.

---

# Data flow

```text
Dashboard
  ↓
ContentService.getDashboardData()
  ↓
HttpContentRepository (VITE_USE_MOCK=false in Docker)
  ↓
GET /api/contents
  ↓
computeStatistics() + recentContents
```

---

# UI behaviour

| State | Display |
|-------|---------|
| Loading | Spinner |
| Error | EmptyState "Unable to load dashboard" |
| Empty DB | EmptyState "No content yet" + stats at 0 |
| Data | RecentContents + Statistics (unchanged layout) |

---

# Next

**S2-06** — Remove remaining mock-only paths / E2E import → library → dashboard
