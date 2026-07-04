import 'dart:io';

import 'package:network_info_plus/network_info_plus.dart';
import 'package:permission_handler/permission_handler.dart';

/// Reads the current Wi‑Fi SSID on Android (location permission required on 10+).
class WifiDetector {
  WifiDetector({NetworkInfo? networkInfo}) : _networkInfo = networkInfo ?? NetworkInfo();

  final NetworkInfo _networkInfo;

  Future<String?> getCurrentSsid() async {
    if (!Platform.isAndroid && !Platform.isIOS) {
      return null;
    }

    if (Platform.isAndroid) {
      final location = await Permission.locationWhenInUse.request();
      if (!location.isGranted) {
        return null;
      }
    }

    final raw = await _networkInfo.getWifiName();
    if (raw == null || raw.isEmpty) {
      return null;
    }

    return raw.replaceAll('"', '').trim();
  }
}
