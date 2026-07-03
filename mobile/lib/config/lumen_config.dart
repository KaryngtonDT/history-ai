import '../services/connection_manager.dart';

/// Personal Remote profile for lp-hp1711 @ home.
/// Update LAN IP if your router assigns a new address.
class LumenConfig {
  static const ConnectionConfig connection = ConnectionConfig(
    mode: ConnectionMode.auto,
    localhostUrl: 'http://127.0.0.1:8000',
    lanUrl: 'http://192.168.43.194:8000',
    tailscaleUrl: 'http://100.111.236.50:8000',
    homeWifiSsids: ['steve'],
  );

  static const String healthPath = '/health';
  static const String mobileHealthPath = '/api/shadow/mobile/health';
}
