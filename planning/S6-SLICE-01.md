# S6-SLICE-01 — PDF Text Extraction

Status: **Done**

Epic: **Epic 06 — Document Processing**

---

# Goal

Extract real text from PDFs and persist a Transcript artifact before the simulated Summary.

---

# Created

```text
worker/app/extractors/PdfExtractor.py
worker/app/services/DocumentExtractionService.py
worker/app/loaders/LocalPdfLoader.py
worker/fixtures/sample.pdf
```

---

# Flow

```text
ProcessingService
  → DocumentExtractionService
  → LocalPdfLoader (storage/{contentId}.pdf or fixtures/sample.pdf)
  → PdfExtractor (pypdf)
  → POST /internal/artifacts (transcript)
  → POST /internal/artifacts (summary placeholder)
  → complete
```

---

# Interim storage

MinIO upload is not ready. Until then:

- `{PDF_STORAGE_DIR}/{contentId}.pdf` when present
- fallback: `worker/fixtures/sample.pdf`

---

# Next

**S6-SLICE-02** — Transcript artifact surfaced in frontend / pipeline hardening
