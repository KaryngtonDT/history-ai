# Tailscale Architecture (Lumen)

Version: 1.0

Status: Active — official transport for Personal Remote profile

## Why Tailscale

For personal and home-server deployments, Lumen does **not** require:

- Public cloud (AWS, Azure, GCP)
- VPS or Kubernetes
- Public reverse proxy or domain name
- Port forwarding or NAT traversal configuration

Tailscale provides a **private mesh VPN**. The home PC runs Docker + Lumen; the phone (or laptop away from home) reaches Lumen via the PC's **Tailscale IP** (e.g. `100.x.x.x`).

## Topology

```text
Android / Laptop (Tailscale client)
              │
         Tailscale mesh (encrypted)
              │
    Home PC (Tailscale + Docker)
              │
         Lumen API :8080 (internal)
```

No ports are opened on the home router. Traffic never transits the public internet unencrypted to arbitrary hosts — only between Tailscale peers.

## Connection modes

| Mode | When | Endpoint |
| ---- | ---- | -------- |
| **Localhost** | Dev on same machine | `127.0.0.1` |
| **LAN** | Same Wi‑Fi as server | `192.168.x.x` or mDNS |
| **Tailscale** | Remote (4G, office, travel) | `100.x.x.x` or MagicDNS |
| **Auto** | Default for Personal Remote | Home Wi‑Fi → LAN; else → Tailscale |

Shadow clients treat transport as **opaque** — the same API and presence semantics apply regardless of mode.

## Recommended profile

**Personal Remote** (Docker + Tailscale) is the **reference mode** for developers and advanced solo users during the pre–public-API phase (~6 months).

## Server requirements

On the **home PC**:

1. Install [Tailscale](https://tailscale.com/download) and sign in
2. Run Lumen via Docker (`make prod-rebuild`, etc.)
3. Note Tailscale IP: `tailscale ip -4`
4. Ensure Lumen binds on `0.0.0.0` inside Docker network (default prod-like stack)

On **mobile**:

1. Install Tailscale app, same tailnet
2. Configure Lumen base URL to Tailscale host (or use Auto detection)
3. Mobile Connection Manager health-checks reachability

## Health checks

Personal Remote Access dashboard reports:

- Server reachable (HTTP)
- Latency / rough bandwidth
- Shadow reachability
- GPU / worker availability (via existing platform health)
- Second Brain sync status

## Security notes

- Tailscale ACLs can restrict which devices reach the Lumen port
- Lumen API auth tokens still apply — Tailscale is **network** layer, not application auth
- Do not expose Lumen directly to `0.0.0.0` on the public WAN interface without Tailscale or another VPN

## Operations

Setup guide: [TAILSCALE_SETUP.md](../operations/TAILSCALE_SETUP.md)

## Related

- [DEPLOYMENT_PROFILES.md](DEPLOYMENT_PROFILES.md)
- [PERSONAL_REMOTE_ACCESS.md](PERSONAL_REMOTE_ACCESS.md)
- [MOBILE_CONNECTION_MANAGER.md](MOBILE_CONNECTION_MANAGER.md)
