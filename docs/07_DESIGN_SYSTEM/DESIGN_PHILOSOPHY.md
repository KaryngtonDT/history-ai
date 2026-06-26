# Design Philosophy

Version: 1.0

Status: Draft

---

# Identity

History AI is **Quiet Intelligence**.

The product should feel like it is thinking — never shouting.

No flashing elements.

No aggressive colors.

No visual clutter.

---

# Three Words

Every design decision must align with:

```text
Calm

Focused

Scholarly
```

When someone opens History AI, they should feel like entering a **modern library** — not a chatbot, not a social feed, not a dashboard of widgets.

---

# What We Are Not

We do not copy:

* ChatGPT (conversational default UI)
* Notion (block editor aesthetic as our shell)
* NotebookLM (their visual language)

We learn from others. We build our own identity.

---

# Inspirations

| Product | What we borrow |
| ------- | -------------- |
| Linear | Simplicity and speed |
| Notion | Visual hierarchy |
| Apple | Space and restraint |
| Stripe | Documentation clarity and readability |
| Arc Browser | Interaction fluidity |
| Perplexity | Knowledge presentation |
| NotebookLM | Document organization (not visual design) |

---

# Content First

Colors, chrome, and UI chrome exist to **support knowledge** — never to compete with it.

The user's material (PDF, transcript, summary, quiz) is always the hero.

---

# One Question Per Screen

Every screen answers exactly one question:

| Screen | Question |
| ------ | -------- |
| Dashboard | What can I learn today? |
| Library | What have I already imported? |
| Content Details | Where is processing? |
| Learning Package | What have I learned? |
| Upload | How do I bring knowledge in? |
| Settings | How do I configure my experience? |

If a screen answers two questions, split it.

---

# Motion

Motion confirms state — it does not decorate.

Prefer subtle transitions over animation for its own sake.

See [ANIMATIONS.md](ANIMATIONS.md).

---

# Relationship to Code

```text
docs/07_DESIGN_SYSTEM/     ← Product Design System (this folder)
design/                    ← Brand assets, wireframes, mockups
frontend/src/components/   ← Implementation (after design validation)
```

Design precedes implementation. No new Feature UI without a validated mockup or wireframe in `design/`.
