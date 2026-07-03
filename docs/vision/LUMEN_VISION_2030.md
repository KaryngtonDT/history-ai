# Lumen Vision 2030

Version: 1.0

Status: **Approved — Constitutional reference**

Author: Product / CTO

---

# What Lumen is today

At launch, Lumen was:

> *An AI-assisted video translation platform.*

After 67 sprints, Lumen is:

> **A personal learning operating system, powered by Shadow, that accompanies the user everywhere they learn.**

This is not a rebrand. It is a change of category.

---

# Product architecture

```text
                    LUMEN
                        │
        ┌───────────────┼────────────────┐
        │               │                │
        ▼               ▼                ▼
 Knowledge         Shadow AI         Learning OS
 Processing        Companion
 (Platform)        (Product)
```

**The website is no longer the product.**

**Shadow is the product.**

Lumen is its engine — pipeline, memory, knowledge, teaching, goals, executive, storage.

---

# Mission

> **Lumen transforms every piece of content (video, audio, PDF, YouTube, documentation, code…) into a personalized learning journey accompanied by Shadow.**

Shadow's purpose is not to answer questions.

Shadow's purpose is:

> **To grow the user intellectually.**

Success is not measured by prompts, responses, or time in the app.

Success is measured by:

- skills acquired
- concepts mastered
- goals reached
- evolution over years

---

# The five pillars

## 1. Learn Anywhere

YouTube, Udemy, Coursera, PDF, Kindle, documentation, Cursor, VS Code, GitHub, conferences, podcasts, audiobooks — Shadow is present.

## 2. Learn Once

You learn once. Shadow remembers forever.

Example: you read a PDF today. Three months later, you watch a video. Shadow says:

> *This concept comes from the PDF we studied in March.*

## 3. Learn Deeply

Shadow does not translate. Shadow builds bridges.

```text
Docker → Container → Linux Namespace → Kernel → Process → CPU Scheduling
```

Progressive depth, not flat summaries.

## 4. Learn Personally

Two people watch the same video. Shadow does not say the same thing — because it knows level, goals, habits, vocabulary, and history.

## 5. Learn Forever

The Second Brain becomes intellectual patrimony. After five years, you can find where you learned a concept, why, in which video, which conversation, which exercises.

---

# Ten non-negotiable principles

| # | Principle |
| - | --------- |
| 1 | **Shadow accompanies the user everywhere** — not only inside Lumen. Shadow never asks "import this content"; it meets content where it is. |
| 2 | **One memory** — video, PDF, audio, GitHub, courses, conversations → Second Brain. Always one source of truth. |
| 3 | **Every interaction builds something** — no disposable answers. Each touch enriches graph, memory, goals, missions, connections. |
| 4 | **The user owns decisions** — Shadow proposes, never decides. |
| 5 | **Every decision is explainable** — always *why*, never *the AI thinks that…* |
| 6 | **Knowledge is connected** — never isolated nodes. |
| 7 | **Progress is the KPI** — competencies acquired, not minutes spent. |
| 8 | **Shadow adapts pedagogy, not facts** — vocabulary, pace, voice, analogies may change; truth does not. |
| 9 | **Local first** — what can stay local stays local. Cloud only when it adds real value. |
| 10 | **Shadow helps think** — it does not replace thinking. |

---

# Four phases

## Phase I — Knowledge Processing ✅ Complete

```text
PDF · Video · Audio · YouTube
```

Unified source ingestion and AI pipeline (Sprints 31–52).

## Phase II — Shadow Intelligence ✅ Complete

```text
Memory · Teaching · Mentor · Executive · Second Brain
```

Personal AI companion stack (Sprints 55–67).

## Phase III — Shadow Everywhere ← **Now**

Shadow lives beyond the browser.

```text
Shadow Presence
      │
      ├── Desktop foundation + Quick Launcher (S68)
      ├── Browser Companion (S69)
      ├── IDE Companion (S70)
      ├── Mobile Companion (S71)
      └── Ambient Shadow (S72)
```

**One intelligence. Multiple points of presence.**

## Phase IV — Learning Ecosystem (Later)

Platform hardening, Public API, SDK, Marketplace, Enterprise.

Deferred until daily Shadow usage is proven at scale.

---

# Roadmap orientation

**Old mental model (obsolete):**

```text
Site Web → API → SDK
```

**New mental model:**

```text
Shadow → Desktop → Browser → IDE → Mobile → Wearables
```

The user always stays with Shadow.

---

# Shadow Presence

The user no longer launches Shadow.

**Shadow is already there.**

- YouTube → discrete icon → *Shadow, explain.*
- Cursor → same behavior
- PDF → same behavior
- GitHub → same behavior

One behavior everywhere. See [SHADOW_PRESENCE.md](SHADOW_PRESENCE.md).

---

# Shadow Continuity

Not memory alone — **intellectual continuity** across days and surfaces.

| Moment | Shadow knows |
| ------ | ------------ |
| Morning — YouTube video | Concepts introduced |
| Afternoon — coding in Cursor | *We are applying what we studied this morning* |
| Evening — PDF | *This section answers yesterday's question* |
| Three weeks later — conference | *You had the same difficulty in April — you master it now* |

See [SHADOW_CONTINUITY.md](SHADOW_CONTINUITY.md).

---

# Platform vs product

```text
                     Shadow
        Desktop · Browser · IDE · Mobile · Voice · Watch
                              │
                              ▼
                    Shadow Context Hub
                              │
                              ▼
                    Shadow Intelligence
         Identity · Voice · Memory · Knowledge · Teaching
              · Mentor · Executive · Second Brain
                              │
                              ▼
                      Lumen Platform
              Pipeline · Storage · Processing · API
```

Lumen processes content. Shadow learns with the user.

---

# Sprint question (from Sprint 68 onward)

Every sprint must answer:

> **How can Shadow accompany the user in more moments of their life, while remaining discrete, explainable, and entirely under their control?**

---

# Non-goals (unchanged)

Lumen / Shadow is NOT:

- a YouTube replacement
- a social network
- a piracy or copyright bypass tool
- an engagement-maximization product

---

# Governance

| Decision type | Reference |
| ------------- | --------- |
| Product direction | This document |
| Shadow features | [Shadow Roadmap](../shadow/ROADMAP.md) |
| Sprint scope | `planning/Shadow/Sprint-XX/TASK-00XX.md` |
| Engineering rules | `engineering/00_ENGINEERING_PRINCIPLES.md` |
| Architecture detail | `docs/architecture/` |

When this document and a task file conflict, **update the task** — not the vision.

---

# Supersedes

This document supersedes the product positioning in:

- `docs/00_PROJECT/VISION.md` (History AI platform framing — retained for history)
- Previous Shadow roadmap pivot "daily friction" (S67–S72 naming) — replaced by Phase III "Shadow Everywhere"
- Any roadmap placing Public API before companion surfaces

Historical sprint verification reports remain valid audit records.
