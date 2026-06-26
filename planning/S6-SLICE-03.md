# S6-SLICE-03 — Deterministic Summary Generator

Status: **Done**

Epic: **Epic 06 — Document Processing**

---

# Goal

Replace simulated summary with a deterministic summary derived from the Transcript.

---

# Created

```text
worker/app/generators/SummaryGenerator.py
```

---

# Rule

First 3 meaningful sentences from transcript; full transcript if shorter; error if empty.

---

# Flow

```text
extract transcript → SummaryGenerator.generate() → summary artifact
```

---

# Next

**S6-SLICE-04** — AI Provider Layer (interface + Mock provider)
