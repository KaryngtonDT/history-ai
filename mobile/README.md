# Shadow Mobile (Flutter)

Light Shadow client for Android and iOS. Intelligence runs on the home Lumen server; the app handles UI, voice I/O, and connection management (Local, LAN, Tailscale, Auto).

## Prerequisites

- Flutter SDK 3.5+
- Tailscale on device for Personal Remote profile

See [docs/operations/ANDROID_SETUP.md](../docs/operations/ANDROID_SETUP.md) and [docs/operations/IOS_SETUP.md](../docs/operations/IOS_SETUP.md).

## Scaffold status

Sprint 70 delivers the **foundation**: Connection Manager module and project layout. Full platform folders (`android/`, `ios/`) are generated when you run:

```bash
cd mobile
flutter create . --platforms=android,ios
flutter pub get
flutter test
```

## Connection Manager

`lib/services/connection_manager.dart` — selects Localhost, LAN, Tailscale, or Auto (home Wi‑Fi → LAN, else Tailscale).
