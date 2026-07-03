# Mobile Connection Manager

Version: 1.0

Status: Planned (Sprint 70)

## Purpose

Abstract **how** the mobile client reaches Lumen so Shadow logic stays transport-agnostic.

## Components

| Component | Role |
| --------- | ---- |
| `ConnectionManager` | Facade: current mode, switch, health aggregate |
| `LocalConnection` | `127.0.0.1` / emulator host |
| `LanConnection` | Subnet scan or configured LAN host + mDNS |
| `TailscaleConnection` | Configured Tailscale IP or MagicDNS name |
| `ConnectionDetector` | SSID / reachability probes for Auto |
| `ConnectionHealth` | Latency, HTTP ping, Shadow `/health` |
| `ConnectionSwitcher` | Seamless failover LAN ↔ Tailscale |

## Modes

```text
enum ConnectionMode {
  localhost,
  lan,
  tailscale,
  auto,      // LAN when home Wi‑Fi, else Tailscale
  cloud,     // reserved — future public HTTPS
}
```

## Auto algorithm (MVP)

```text
if (connectedToKnownHomeWifi && lanHostReachable)
  use LanConnection
else if (tailscaleHostReachable)
  use TailscaleConnection
else
  show offline / retry with exponential backoff
```

Known home Wi‑Fi: user-configured SSID list in preferences (or first-seen pairing flow).

## Configuration storage

- **Mobile:** secure local storage (Flutter `shared_preferences` or encrypted store) for base URLs per mode
- **Backend:** `PUT /api/shadow/mobile/connection` persists profile for cross-client settings sync (optional MVP)

## Health probe sequence

1. TCP/HTTP reachability to Lumen base URL
2. `GET /api/shadow/mobile/health` or platform readiness
3. Optional: GPU/worker flags from server payload

## Failure UX

```text
Connexion perdue
Tentative via Tailscale…
✓ Reconnecté
```

`ConnectionSwitcher` runs probes in order defined by active profile without user intervention.

## Extension to other clients

Desktop and Browser may reuse the same **concept** (settings → connections); Sprint 70 implements full manager on **mobile** first. Web settings at `/settings/connections` align profiles for documentation and manual override.

## Related

- [TAILSCALE_ARCHITECTURE.md](TAILSCALE_ARCHITECTURE.md)
- [SHADOW_MOBILE.md](SHADOW_MOBILE.md)
