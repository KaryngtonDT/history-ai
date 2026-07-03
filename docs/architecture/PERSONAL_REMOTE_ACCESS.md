# Personal Remote Access

Version: 1.0

Status: Active (Sprint 70)

## Model

**Home Server First.** The authoritative Lumen instance runs at home (Docker prod-like). Clients are thin surfaces that connect over a **transport layer** the user does not need to think about when **Auto** mode is enabled.

## Two everyday modes

### Mode 1 — Local network

```text
Phone ── Wi‑Fi home ──► Lumen (LAN IP)
```

Low latency, no VPN overhead.

### Mode 2 — Private remote (recommended away from home)

```text
Phone ── 4G/5G ──► Tailscale ──► Home PC ──► Docker ──► Lumen
```

Same Shadow, same memory, same models — computation stays on the RTX / home server.

## One Shadow across clients

```text
Desktop ──┐
Browser ──┼──► One Lumen @ home ──► One Second Brain
Mobile  ──┘
```

All clients may use Tailscale when not on LAN; Desktop and Browser on the same PC typically use localhost or LAN.

## User-facing settings

**Settings → Connections**

```text
○ Localhost
○ LAN
● Auto          ← recommended
○ Tailscale
○ Cloud         (disabled / future)
```

**Health dashboard** (mobile + web `/settings/server`):

- Server reachable
- Connection mode active
- Latency
- GPU / workers
- Docker / containers summary
- Shadow / Second Brain subsystem status

## Automatic switching (Auto)

1. Detect known home Wi‑Fi SSID → use **LAN** endpoint
2. Otherwise → use **Tailscale** endpoint (configured MagicDNS or IP)
3. On failure → retry alternate path, show reconnect UX (no manual toggle required)

## What this sprint does not do

- Public multi-tenant cloud hosting
- Android Auto / WearOS
- Full offline queue (notifications may queue; AI requires server)

## Related

- [TAILSCALE_ARCHITECTURE.md](TAILSCALE_ARCHITECTURE.md)
- [HOME_SERVER.md](HOME_SERVER.md)
- [MOBILE_CONNECTION_MANAGER.md](MOBILE_CONNECTION_MANAGER.md)
