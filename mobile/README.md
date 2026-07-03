# Shadow Mobile (Flutter)

Light Shadow client for Android and iOS. Intelligence runs on the home Lumen server; the app handles UI and connection management (Local, LAN, Tailscale, Auto).

## Prerequisites (Windows)

1. **Flutter SDK** — installed at `C:\src\flutter` (add to PATH):
   ```powershell
   [Environment]::SetEnvironmentVariable("Path", "C:\src\flutter\bin;" + $env:Path, "User")
   ```
2. **Android Studio** — for Android SDK + device/emulator: https://developer.android.com/studio  
   After install: `flutter doctor --android-licenses`
3. **Tailscale** on PC and tablet/phone (same account)

Verify:

```powershell
flutter doctor
```

## Configuration

Personal Remote URLs are in `lib/config/lumen_config.dart`:

| Setting | Current value |
|---------|---------------|
| Tailscale | `http://100.111.236.50:8000` |
| LAN | `http://192.168.43.194:8000` |
| Home Wi‑Fi | `steve` |

Re-run after IP changes:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/configure-personal-remote.ps1
```

## Commands

```powershell
cd mobile
flutter pub get
flutter test
flutter devices
flutter run          # USB device or emulator
flutter build apk --release
```

APK output: `build/app/outputs/flutter-apk/app-release.apk`

## Install on Lenovo tablet

1. Copy APK to tablet (USB, email, or cloud)
2. Enable **Install unknown apps** for your file manager
3. Install APK
4. **Tailscale ON** on tablet
5. Open **Shadow Mobile** → **Check Lumen**

Or sideload via USB:

```powershell
flutter install
```

## Notes

- HTTP cleartext is enabled for private Tailscale/LAN (no public exposure)
- Tablet without SIM: use phone hotspot + Tailscale for remote testing
- See [TAILSCALE_SETUP.md](../docs/operations/TAILSCALE_SETUP.md)
