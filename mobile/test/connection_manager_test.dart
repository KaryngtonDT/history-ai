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

    test('auto uses LAN on home wifi SSID', () {
      final manager = ConnectionManager(config: config);

      expect(
        manager.resolveEndpoint(currentSsid: 'HomeWiFi'),
        config.lanUrl,
      );
    });

    test('auto uses Tailscale on hotspot steve', () {
      final manager = ConnectionManager(config: config);

      expect(
        manager.resolveEndpoint(currentSsid: 'steve'),
        config.tailscaleUrl,
      );
    });

    test('auto uses Tailscale away from home', () {
      final manager = ConnectionManager(config: config);

      expect(
        manager.resolveEndpoint(currentSsid: 'OtherNetwork'),
        config.tailscaleUrl,
      );
    });

    test('auto candidates prefer LAN then Tailscale at home', () {
      final manager = ConnectionManager(config: config);

      expect(
        manager.candidateEndpoints(currentSsid: 'HomeWiFi'),
        [config.lanUrl, config.tailscaleUrl],
      );
    });

    test('auto candidates prefer Tailscale on hotspot', () {
      final manager = ConnectionManager(config: config);

      expect(
        manager.candidateEndpoints(currentSsid: 'steve'),
        [config.tailscaleUrl, config.lanUrl],
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
        manager.resolveEndpoint(currentSsid: 'HomeWiFi'),
        'http://100.64.0.1:8080',
      );
    });

    test('forceTailscale override', () {
      final manager = ConnectionManager(config: config);

      expect(
        manager.resolveEndpoint(
          currentSsid: 'HomeWiFi',
          forceTailscale: true,
        ),
        config.tailscaleUrl,
      );
    });
  });
}
