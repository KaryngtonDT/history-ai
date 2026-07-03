# Deployment Profiles

Version: 1.0

Status: Active

Lumen ships **one codebase**. Deployment profile selects **where** it runs and **how** clients connect.

## Profiles

| Profile | Audience | Hosting | Transport | Sprint |
| ------- | -------- | ------- | --------- | ------ |
| **Personal Local** | Developer, solo | Docker on dev PC | Localhost | ✅ now |
| **Personal Remote** ⭐ | Solo user (recommended) | Docker on home PC | **Tailscale** (+ LAN at home) | Sprint 70 |
| **Home Server** | Family / small team | Docker on NAS or PC | Tailscale, multi-user | Post–S70 |
| **Cloud** | Public API, enterprise | K8s / managed | HTTPS public | Phase IV+ |

## Personal Local

```text
Client → localhost:8080 → Docker → Lumen
```

Use for development, `make doctor`, PHPUnit, extension testing.

## Personal Remote (recommended)

```text
Client → Tailscale → home PC → Docker → Lumen
```

At home on Wi‑Fi: **Auto** uses LAN for lower latency.

Away: **Tailscale** — no port forwarding.

Official docs: [TAILSCALE_ARCHITECTURE.md](TAILSCALE_ARCHITECTURE.md).

## Home Server

Extends Personal Remote:

- Dedicated or NAS hardware
- 2–5 users on same tailnet
- Shared Second Brain policies (future)

## Cloud

Future public deployment:

- Reverse proxy, TLS, tenant isolation
- Same Shadow client code; `ConnectionMode.cloud`

Not in Sprint 70 scope.

## Client settings mapping

| UI label | Profile / mode |
| -------- | -------------- |
| Localhost | Personal Local |
| LAN | Personal Local / Home Server (same network) |
| Auto | Personal Remote |
| Tailscale | Personal Remote (forced) |
| Cloud | Cloud (disabled until Phase IV) |

## Related

- [HOME_SERVER.md](HOME_SERVER.md)
- [PERSONAL_REMOTE_ACCESS.md](PERSONAL_REMOTE_ACCESS.md)
- [../operations/DEPLOYMENT_GUIDE.md](../operations/DEPLOYMENT_GUIDE.md)
