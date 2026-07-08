# Delivery Roadmap

Version: 3.0

Status: Active

Constitutional reference: [LUMEN_VISION_2030.md](../docs/vision/LUMEN_VISION_2030.md)

See [WORKFLOW.md](WORKFLOW.md) for roles, cycle, and quality gates.

---

# What we build now

**Lumen** = learning platform engine (ingestion, pipeline, storage).

**Shadow** = product — AI companion that accompanies the user everywhere they learn.

Current chapter: **Platform Sprint 72** (AI Engine Management Platform) complete; **Phase III — Shadow Everywhere** continues with Shadow Sprint 72 (Ambient Shadow).

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

## Platform Sprint 70.4 — AI Runtime, Engine Intelligence & Benchmark Platform ← **next**

**Task:** [planning/Platform/Sprint-70.4/TASK-0070.4.md](Platform/Sprint-70.4/TASK-0070.4.md)

Merged former Platform Sprints **70.4 + 70.5** into one infrastructure chapter: Runtime, Registry, Discovery, Readiness, Launcher, Health, Benchmark, Intelligence, Catalog, Validation, Explainability. Host-installed engines + Docker application stack only. **Last major IA infrastructure sprint before returning to Shadow.**

## Sprint 69.5 — Shadow Browser Experience Completion — **in progress**

**Task:** [planning/Shadow/Sprint-69.5/TASK-0069.5.md](Shadow/Sprint-69.5/TASK-0069.5.md)

Connect overlay actions to backend APIs; loading, results, toasts, Open Watch import flow.

## Sprint 70 — Shadow Mobile Companion & Personal Remote Access — **Complete**

**Task:** [planning/Shadow/Sprint-70/TASK-0070.md](Shadow/Sprint-70/TASK-0070.md)

Mobile API, web settings (Mobile, Connections, Server), Flutter Connection Manager foundation, Tailscale as official Personal Remote transport.

## Sprint 71 — Shadow IDE Companion ← **after Platform 70.4**

**Task:** [planning/Shadow/Sprint-71/TASK-0071.md](Shadow/Sprint-71/TASK-0071.md) — **Planned**

| Sprint | Task | Summary |
| ------ | ---- | ------- |
| 68 | [TASK-0068](Shadow/Sprint-68/TASK-0068.md) | Shadow Everywhere foundation, Tauri, Quick Launcher |
| 69 | [TASK-0069](Shadow/Sprint-69/TASK-0069.md) | Browser Companion (MV3 extension) |

---

# Next chapter (Phase III continued)

| Sprint | Name |
| ------ | ---- |
| **70.8** | **Background pipeline orchestration** ([TASK-0070.8](Platform/Sprint-70.8/TASK-0070.8.md)) |
| **70.9** | **Engine performance analytics & adaptive estimation** ([TASK-0070.9](Platform/Sprint-70.9/TASK-0070.9.md)) |
| **71** | **Capability platform** — 33 engines, 10 capabilities ([TASK-0071](Platform/Sprint-71/TASK-0071.md)) |
| **71.1** | **Runtime Kernel unification** — Resolver, Pipeline/Doctor convergence ([TASK-0071.1](Platform/Sprint-71.1/TASK-0071.1.md)) |
| **72** | **AI Engine Management Platform** — lifecycle, Provision Center, Auto/Manual/Locked, resolver intelligence ([TASK-0072](Platform/Sprint-72/TASK-0072.md)) |
| 71 (Shadow) | IDE Companion ([TASK-0071](Shadow/Sprint-71/TASK-0071.md)) |
| 72 (Shadow) | Ambient Shadow |

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

Platform infrastructure sprints use:

```text
planning/Platform/Sprint-XX/TASK-00XX.md
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
