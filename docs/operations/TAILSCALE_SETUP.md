# Tailscale Setup for Lumen

Version: 1.0

Status: Active

## Goal

Enable **Personal Remote** access: phone and laptops reach home Lumen via Tailscale without opening router ports.

## Prerequisites

- Lumen running via Docker on home PC (`make prod-rebuild`, `make migrate`, `make doctor`)
- Tailscale account (free tier sufficient for personal use)

## Home PC (Lumen host)

### 1. Install Tailscale

Download from [tailscale.com/download](https://tailscale.com/download) and sign in.

### 2. Verify tailnet IP

```bash
tailscale ip -4
```

Example: `100.64.12.34`

### 3. Confirm Lumen is reachable locally

```bash
curl -s http://127.0.0.1:8000/api/health
```

Adjust port if your `docker-compose` maps differently (prod-like default: **8000**).

### 4. Confirm Lumen via Tailscale IP

From another device on the tailnet (or phone on cellular with Tailscale):

```bash
curl -s http://100.64.12.34:8000/api/health
```

If this fails, check Docker port binding (`0.0.0.0:8080`) and Windows firewall rules for Tailscale interface.

### 5. Optional — MagicDNS

In Tailscale admin console, enable MagicDNS. Use hostname instead of IP:

```text
http://home-pc.tailnet-name.ts.net:8080
```

## Android

1. Install **Tailscale** from Play Store
2. Sign in to the **same tailnet** as the home PC
3. In Shadow Mobile → **Connections**, set:
   - **Auto** (recommended), or
   - **Tailscale** with base URL `http://100.x.x.x:8080` (or MagicDNS)
4. Verify health dashboard shows **Server reachable**

## iOS

Same as Android using Tailscale iOS app.

## Desktop / Browser (away from home)

- Install Tailscale on laptop
- Point Browser extension or Desktop app to Tailscale URL when not on home LAN
- At home, **Auto** uses LAN

## Security

- Use Tailscale ACLs to limit which devices can reach port 8080
- Keep Lumen authentication enabled; Tailscale is not a substitute for API tokens
- Do not port-forward 8080 on the router for this profile

## Troubleshooting

| Symptom | Check |
| ------- | ----- |
| Timeout on Tailscale IP | Firewall, Docker bind address, Tailscale connected |
| Works on LAN, not Tailscale | Windows firewall profile for Tailscale adapter |
| SSL errors | Personal Remote uses HTTP on tailnet; TLS optional later |
| Wrong Shadow instance | Single home server — verify URL in Connections |

## Related

- [TAILSCALE_ARCHITECTURE.md](../architecture/TAILSCALE_ARCHITECTURE.md)
- [PERSONAL_REMOTE_ACCESS.md](../architecture/PERSONAL_REMOTE_ACCESS.md)
- [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)
