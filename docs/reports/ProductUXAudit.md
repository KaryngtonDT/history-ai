# Platform Sprint 50 — Product UX Audit

Version: 1.0

Date: 2026-07-01

Status: Accepted (pre-implementation baseline)

---

# Executive summary

History AI has **complete backend capabilities** (Sprints 31–49) and **matching frontend routes**, but poor **product coherence**. The video pipeline (6 step pages), AI settings, and post-upload flows are largely **undiscoverable** without typing URLs. Workspace embeds batch, collaboration, reviews, telemetry, and history but does not link videos to pipeline steps.

**Keyword:** product coherence — connect existing features, explain them, guide next actions.

---

# Global product map

```text
Upload → Transcript → Translation → Audio → Voice Clone → Lip Sync → Render → Quality
                                                                              ↓
Workspace ← Batch ← History ← Reviews ← Preferences ← Team ← Analytics
     ↑
Pipeline / AI Engines / Automatic Mode / Intelligence / Optimization / Scheduler
```

---

# Route inventory

| Route | Page | Sprint | Backend capability | User goal | Discoverability (1–5) | Complexity (1–5) | Proposed improvement |
| ----- | ---- | ------ | ------------------ | --------- | --------------------- | ---------------- | -------------------- |
| `/` | Dashboard | M1 | Contents overview | See recent work | 5 | 2 | Link recent items to destinations |
| `/import` | Import | M1 | PDF/audio import | Import documents | 5 | 3 | Distinguish PDF vs audio labels |
| `/video/upload` | Video upload | 31–43 | Video upload + orchestration | Upload and process video | 3 | 4 | Add to sidebar; link to pipeline after upload |
| `/video/:id/transcript` | Transcript | 32 | STT / Faster-Whisper | Read transcript | 1 | 2 | Journey map + upload success links |
| `/video/:id/translations` | Translations | 33 | Ollama translation | View/edit translations | 1 | 3 | Journey map links |
| `/video/:id/audio` | Audio | 35 | F5-TTS | Preview audio | 1 | 3 | Journey map links |
| `/video/:id/voice-clone` | Voice clone | 36 | OpenVoice | Clone voice | 1 | 4 | Help + journey links |
| `/video/:id/lip-sync` | Lip sync | 37 | LatentSync | Preview lip sync | 1 | 4 | Help + journey links |
| `/video/:id/render` | Final render | 38 | FFmpeg render | Download MP4 | 1 | 3 | Journey map terminus |
| `/workspace` | Workspace | 45–49 | Projects, batch, team, analytics | Manage projects | 5 | 5 | Link videos to pipeline; add upload path |
| `/settings` | Settings hub | — | — | Configure app | 4 | 1 | Replace stub with real links |
| `/settings/ai` | AI engines | 34 | AI registry | Choose engines | 1 | 3 | Link from settings hub + sidebar |
| `/settings/pipeline` | Pipeline | 39 | Pipeline config | Configure stages | 1 | 4 | Link from settings hub + sidebar |
| `/library` | Library | M1 | Library items | Browse saved content | 5 | 2 | — |
| `/library/:id` | Library item | M1 | Artifacts | View item details | 4 | 3 | — |
| `/collections` | Collections | M1 | Collections | Organize content | 5 | 2 | — |
| `/processing/:id` | Processing | M1 | Processing jobs | Monitor PDF pipeline | 2 | 5 | Sidebar link for discoverability |

---

# Embedded capabilities (no dedicated route)

| Capability | Location | Sprint | Discoverability | Improvement |
| ---------- | -------- | ------ | --------------- | ----------- |
| Quality report | Upload preview only | 44 | 2 | Post-upload quality view + journey |
| Video intelligence | Upload (automatic mode) | 41 | 3 | Explain recommendations |
| Optimization | Upload (automatic mode) | 42 | 3 | Help drawer |
| Scheduler | Upload (automatic mode) | 43 | 3 | Help drawer |
| History / reprocess | Workspace panel | 46 | 4 | Dedicated section in nav |
| Reviews | Workspace panel | 47 | 4 | Help + next-step guide |
| Preferences | Workspace panel | 47 | 3 | Help content |
| Collaboration | Workspace panel | 48 | 4 | — |
| Analytics / telemetry | Workspace panel | 49 | 4 | Sidebar group link |

---

# Feature modules (29)

`agent`, `ai`, `analytics`, `audio`, `chat`, `collaboration`, `collection`, `dashboard`, `graph`, `history`, `import`, `intelligence`, `library`, `lipsync`, `map`, `optimization`, `orchestrator`, `pipeline`, `processing`, `quality`, `recommendation`, `render`, `review`, `scheduler`, `semantic`, `transcript`, `translation`, `video`, `voice`, `workspace`

---

# Services (38)

All follow Repository → Service pattern. No direct fetch in features (verified pattern).

---

# Missing navigation (priority)

1. Upload success → pipeline steps (transcript through render)
2. Workspace video grid → per-video pipeline links
3. Settings stub → `/settings/ai` and `/settings/pipeline`
4. Sidebar missing: Upload Video, Pipeline, AI Engines, Analytics context
5. Video upload not in sidebar (dashboard only)
6. Post-upload quality (service exists, no UI route)

---

# Duplicated / confusing entry points

| Issue | Resolution |
| ----- | ---------- |
| Dashboard "Import PDF" and "Import Audio" → same `/import` | Differentiate or merge label |
| Settings sidebar → stub page | Settings hub with child links |
| Quality on upload vs analytics in workspace | Cross-link in help; unified journey |
| Automatic mode on upload vs hardcoded in workspace batch | Document in help; workspace uses automatic |

---

# Sprint 50 implementation plan

| Slice | Focus |
| ----- | ----- |
| P50-00 | This audit (no visual changes except broken settings links) |
| P50-01 | ProductShell, AppSidebar, breadcrumbs, page headers |
| P50-02 | Help system + Feature Academy content |
| P50-03 | Artifact journey map integration |
| P50-04 | Command palette, global search, Explain This, tours |
| P50-05 | Polish, verification report, full validation |

---

# CTO pre-checklist

- [ ] No new backend features invented
- [ ] No fake routes
- [ ] Repository/Service pattern preserved
- [ ] Every Sprint 31–49 capability reachable
- [ ] Every artifact clickable or explained
