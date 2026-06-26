# S2-SLICE-04 — Wire Import to Real Backend

Status: **Done**

Epic: **Epic 02 — Real Content Flow**

---

# Goal

Import creates a real Content via `POST /api/contents`. No file upload yet.

---

# Data flow

```text
Import PDF
  ↓
validatePdf()
  ↓
simulateUpload() (UI progress only)
  ↓
ContentService.importPdf()
  ↓
HttpContentRepository → POST /api/contents
  ↓
UploadSuccess (content id)
```

---

# Rules

- Title = PDF filename without `.pdf`
- `sourceType: "pdf"` → mapped to API `upload_pdf` (see TECH-DEBT-sourceType-alignment.md)
- No MinIO, no ProcessingJob, no binary upload
- Mock preserved for Vitest (`VITE_USE_MOCK=true`)

---

# Next

**S2-05** — Wire Dashboard to real backend (or E2E import → library)
