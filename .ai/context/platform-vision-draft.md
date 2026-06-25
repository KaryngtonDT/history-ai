# Platform Vision — Draft

**Status:** Accepted — see `.ai/context/decisions-log.md` Decision 003

**Author:** CTO proposal — 2026-06-25

---

# Proposal

Do not build "an History AI app".

Build a **Learning Platform** — AI-driven generation of learning experiences.

**History AI** = first vertical product.

---

# Pipeline (generic)

```text
Source Content
      ↓
Transcription (if audio/video)
      ↓
Translation
      ↓
Analysis / Knowledge extraction
      ↓
Learning assets (summary, quiz, podcast, …)
      ↓
Learning Package
```

Same pipeline could serve: PDF, EPUB, articles, MOOCs, Wikipedia, internal docs.

---

# Modular vision (future)

```text
Learning Platform
├── History vertical
├── Philosophy vertical
├── Finance vertical
└── Law vertical
```

Goal: ~95% code reuse across verticals.

---

# Architectural implications (if approved)

| Area | Current (MVP) | Platform-oriented |
| ---- | ------------- | ----------------- |
| Product name | History AI | Platform + History module |
| Backend modules | Content, Learning, Knowledge… | + Vertical config / theming |
| Domain language | History-centric | Source-agnostic + vertical overlay |
| Branding | History AI | Per-vertical products |

---

# Recommendation (Software Architect)

**Do not rename modules or restructure code during Milestone 1.**

Reasons:

1. Milestone 1 is infrastructure — no vertical logic yet
2. Current `SYSTEM_BLUEPRINT` modules (Content, Learning, Knowledge) are already **source-agnostic**
3. Premature "platform" naming adds abstraction without Milestone 1 benefit

**Suggested path:**

1. Complete Milestone 1 (Foundation)
2. Formalize platform vision in product docs + ADR
3. Rename/reframe at Milestone 2 kickoff if approved

---

# Decision required

CTO to approve or reject before changing:

* `docs/00_PROJECT/VISION.md`
* `DOMAIN_MODEL.md`
* Module naming in `SYSTEM_BLUEPRINT.md`

Until approved, all implementation remains **History AI MVP**.
