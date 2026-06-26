# S4-SLICE-04 — End-to-End Processing Flow

Status: **Done**

Epic: **Epic 04 — Asynchronous Processing Engine**

---

# Goal

Connect the full user workflow: Import PDF → Create Content → Create ProcessingJob → Processing page with live updates → Completed.

---

# Flow

```text
/import
  → ContentService.importPdf()
  → ProcessingService.createProcessingJob(contentId, "summary")
  → navigate(/processing/{jobId})
  → PollingProcessingMonitor
  → completed
```

---

# Out of scope

- Real file upload / MinIO
- AI / OCR / Whisper

---

# Next

Real processing steps or SSE monitor
