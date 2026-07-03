import 'package:flutter_test/flutter_test.dart';
import 'package:shadow_mobile/services/connection_manager.dart';

void main() {
  group('ConnectionManager', () {
    const config = ConnectionConfig(
      mode: ConnectionMode.auto,
      localhostUrl: 'http://127.0.0.1:8080',
      lanUrl: 'http://192.168.1.10:8080',
      tailscaleUrl: 'http://100.64.0.1:8080',
      homeWifiSsids: ['HomeWiFi'],
    );

    test('auto uses LAN on home wifi', () {
      final manager = ConnectionManager(config: config);

      expect(
        manager.resolveEndpoint(isOnHomeWifi: true, lanReachable: true),
        config.lanUrl,
      );
    });

    test('auto uses Tailscale away from home', () {
      final manager = ConnectionManager(config: config);

      expect(
        manager.resolveEndpoint(isOnHomeWifi: false),
        config.tailscaleUrl,
      );
    });

    test('forced tailscale mode', () {
      final manager = ConnectionManager(
        config: const ConnectionConfig(
          mode: ConnectionMode.tailscale,
          localhostUrl: 'http://127.0.0.1:8080',
          lanUrl: 'http://192.168.1.10:8080',
          tailscaleUrl: 'http://100.64.0.1:8080',
          homeWifiSsids: [],
        ),
      );

      expect(
        manager.resolveEndpoint(isOnHomeWifi: true),
        'http://100.64.0.1:8080',
      );
    });
  });
}
