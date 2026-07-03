# Delivery Roadmap

Version: 3.0

Status: Active

Constitutional reference: [LUMEN_VISION_2030.md](../docs/vision/LUMEN_VISION_2030.md)

See [WORKFLOW.md](WORKFLOW.md) for roles, cycle, and quality gates.

---

# What we build now

**Lumen** = learning platform engine (ingestion, pipeline, storage).

**Shadow** = product — AI companion that accompanies the user everywhere they learn.

Current chapter: **Phase III — Shadow Everywhere** (Sprint 68+).

---

# Completed chapters

## Phase I — Knowledge Processing (Sprints 1–52)

Infrastructure, content pipeline, video/audio/YouTube processing, workspace, i18n, Lumen rebrand.

## Phase II — Shadow Intelligence (Sprints 55–67)

Shadow companion stack from Watch through Second Brain.

| Milestone | Sprint | Status |
| --------- | ------ | ------ |
| Watch companion | 55–56 | ✅ |
| Adaptive learning | 57 | ✅ |
| Identity & voice | 58 | ✅ |
| Relationship & memory | 61–62 | ✅ |
| Teaching & knowledge | 63–64 | ✅ |
| Mentor & executive | 65–66 | ✅ |
| Second Brain | 67 | ✅ |

---

# Current focus

## Sprint 68 — Shadow Everywhere Foundation

**Task:** [planning/Shadow/Sprint-68/TASK-0068.md](Shadow/Sprint-68/TASK-0068.md) — **Complete**

Foundation for Shadow Presence: domain, Context Hub, universal conversation, presence settings, explainability — plus Tauri foundation and minimal Desktop Quick Launcher.

---

# Next chapter (Phase III continued)

| Sprint | Name |
| ------ | ---- |
| **69** | **Browser Companion** |
| 70 | IDE Companion |
| 71 | Mobile Companion |
| 72 | Ambient Shadow |

Detail: [docs/shadow/ROADMAP.md](../docs/shadow/ROADMAP.md)

---

# Phase IV (deferred)

Platform hardening, Public API, SDK, Marketplace, Enterprise — after companion surfaces prove daily usage.

---

# Legacy planning

Early milestone docs (`Milestone-01/`, `MVP-SPRINT-01`, `SPRINT-03`, Epic PDF Upload contracts) remain as **historical records** for foundation work. They are not the active delivery model for Shadow sprints.

Shadow sprints use:

```text
docs/vision/LUMEN_VISION_2030.md
        ↓
planning/Shadow/Sprint-XX/TASK-00XX.md
        ↓
Implementation + docs/reports/SprintXX-Verification.md
```

---

# Governance

| Layer | Document |
| ----- | -------- |
| Vision | [LUMEN_VISION_2030.md](../docs/vision/LUMEN_VISION_2030.md) |
| Shadow product | [docs/shadow/ROADMAP.md](../docs/shadow/ROADMAP.md) |
| Engineering | [Constitution](../engineering/00_ENGINEERING_PRINCIPLES.md) |
| Architecture index | [docs/architecture/README.md](../docs/architecture/README.md) |

---

# Quality gate (every sprint)

```bash
docker compose -f docker-compose.prod-like.yml exec -T backend php bin/phpunit
docker compose -f docker-compose.prod-like.yml exec -T backend composer architecture
docker compose -f docker-compose.prod-like.yml exec -T frontend npm test -- --run
docker compose -f docker-compose.prod-like.yml exec -T frontend npm run check
docker compose -f docker-compose.prod-like.yml exec -T worker pytest
docker compose -f docker-compose.prod-like.yml exec -T worker ruff check .
```
