# iOS Setup (Shadow Mobile)

Version: 1.0

Status: Planned (Sprint 70)

## Prerequisites

- macOS with Xcode
- Flutter SDK (pinned in `mobile/` when scaffold lands)
- Apple Developer account (for device testing)
- Home Lumen reachable via LAN or Tailscale

## Tailscale

1. Install Tailscale from App Store
2. Join same tailnet as home PC
3. Configure Connections in Shadow Mobile (see [TAILSCALE_SETUP.md](TAILSCALE_SETUP.md))

## Build (after Sprint 70 scaffold)

```bash
cd mobile
flutter pub get
flutter run
```

Release:

```bash
flutter build ios --release
```

## Related

- [SHADOW_MOBILE.md](../architecture/SHADOW_MOBILE.md)
- [TASK-0070](../../planning/Shadow/Sprint-70/TASK-0070.md)
