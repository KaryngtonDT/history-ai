# Android Setup (Shadow Mobile)

Version: 1.0

Status: Planned (Sprint 70)

## Prerequisites

- Flutter SDK (version pinned in `mobile/` when scaffold lands)
- Android Studio or command-line SDK
- Home Lumen instance reachable (LAN or Tailscale)
- Tailscale app on device for Personal Remote testing

## Tailscale

1. Install Tailscale from Play Store
2. Join same tailnet as home PC
3. Configure Connections in Shadow Mobile (see [TAILSCALE_SETUP.md](TAILSCALE_SETUP.md))

## Build (after Sprint 70 scaffold)

```bash
cd mobile
flutter pub get
flutter run
```

Production APK:

```bash
flutter build apk --release
```

## Dev tips

- Use `adb reverse` only for USB localhost testing — prefer LAN/Tailscale to match production
- Reload after backend API changes; mobile uses HTTP repository

## Related

- [SHADOW_MOBILE.md](../architecture/SHADOW_MOBILE.md)
- [TASK-0070](../../planning/Shadow/Sprint-70/TASK-0070.md)
