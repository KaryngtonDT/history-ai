# Android Setup (Shadow Mobile)

Version: 1.0

Status: Planned (Sprint 70)

## Prerequisites

- Flutter SDK at `C:\src\flutter` — run `scripts/setup-flutter.ps1` to add PATH
- Android Studio (SDK) — required for `flutter run` / `flutter build apk`
- Home Lumen reachable (LAN or Tailscale)
- Tailscale app on device for Personal Remote testing

## Quick start

```powershell
powershell -ExecutionPolicy Bypass -File scripts/setup-flutter.ps1
cd mobile
flutter pub get
flutter test
flutter devices
flutter run
```

Release APK:

```powershell
flutter build apk --release
# mobile/build/app/outputs/flutter-apk/app-release.apk
```

URLs are configured in `mobile/lib/config/lumen_config.dart` (Tailscale `100.111.236.50:8000`).

## Dev tips

- Use `adb reverse` only for USB localhost testing — prefer LAN/Tailscale to match production
- Reload after backend API changes; mobile uses HTTP repository

## Related

- [SHADOW_MOBILE.md](../architecture/SHADOW_MOBILE.md)
- [TASK-0070](../../planning/Shadow/Sprint-70/TASK-0070.md)
