# Shadow Capability Matrix

Version: 1.1

Status: Active

Each Shadow Client exposes a subset of platform capabilities. Intelligence remains on the **home Lumen server**; clients are presence surfaces.

| Capability | Web | Desktop | Browser | IDE | Mobile |
|------------|:---:|:-------:|:-------:|:---:|:------:|
| Voice | ✅ | ✅ | ✅ | — (S71) | ✅ |
| Watch Companion | ✅ | ✅ | ✅ (YouTube) | — | ✅ |
| Second Brain | ✅ | ✅ | ✅ | — | ✅ |
| Teaching | ✅ | ✅ | ✅ | — | ✅ |
| Mentor | ✅ | ✅ | ✅ | — | ✅ |
| Executive | ✅ | ✅ | ✅ | — | ✅ |
| Knowledge Graph | ✅ | ✅ | ✅ | — | ✅ |
| File Import | ✅ | ✅ | ⚠️ | — | ⚠️ |
| Personal Remote (Tailscale) | ⚠️ | ⚠️ | ⚠️ | — | ✅ |

Legend: ✅ supported · ⚠️ limited · — not yet · ❌ not applicable

**Sprint 69** ✅ Browser column MVP.

**Sprint 70** ✅ Mobile column + official **Tailscale** transport ([DEPLOYMENT_PROFILES.md](../architecture/DEPLOYMENT_PROFILES.md)). IDE column deferred to Sprint 71.
