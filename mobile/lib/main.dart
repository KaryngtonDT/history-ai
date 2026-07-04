import 'package:flutter/material.dart';

import 'config/lumen_config.dart';
import 'services/connection_manager.dart';
import 'services/connection_probe.dart';
import 'services/wifi_detector.dart';

void main() {
  runApp(const ShadowMobileApp());
}

class ShadowMobileApp extends StatelessWidget {
  const ShadowMobileApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Shadow Mobile',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.deepPurple),
        useMaterial3: true,
      ),
      home: const HomeScreen(),
    );
  }
}

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late ConnectionManager _manager;
  late ConnectionProbe _probe;
  final WifiDetector _wifiDetector = WifiDetector();

  ConnectionProbeResult? _result;
  bool _checking = false;
  bool _forceTailscale = false;

  @override
  void initState() {
    super.initState();
    _manager = ConnectionManager(config: LumenConfig.connection);
    _probe = ConnectionProbe(manager: _manager);
    _checkHealth();
  }

  Future<void> _checkHealth() async {
    setState(() => _checking = true);

    final ssid = await _wifiDetector.getCurrentSsid();
    final result = await _probe.check(
      currentSsid: ssid,
      forceTailscale: _forceTailscale,
    );

    if (!mounted) {
      return;
    }

    setState(() {
      _result = result;
      _checking = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final connected = _result?.health.reachable ?? false;
    final endpoint = _result?.endpoint ?? _manager.config.tailscaleUrl;
    final ssid = _result?.currentSsid;
    final onHomeWifi = _result?.onHomeWifi ?? false;
    final activeMode = endpoint == _manager.config.lanUrl
        ? 'lan'
        : endpoint == _manager.config.tailscaleUrl
            ? 'tailscale'
            : _manager.config.mode.name;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Shadow Mobile'),
        actions: [
          IconButton(
            onPressed: _checking ? null : _checkHealth,
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh health',
          ),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: ListView(
          children: [
            Text(
              connected ? 'Shadow connected' : 'Shadow unreachable',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(height: 8),
            _StatusChip(label: 'Route: $activeMode', ok: connected),
            const SizedBox(height: 8),
            _StatusChip(
              label: connected ? 'Server: ok' : 'Server: unavailable',
              ok: connected,
            ),
            const SizedBox(height: 16),
            Text('Endpoint', style: Theme.of(context).textTheme.titleMedium),
            SelectableText(endpoint),
            const SizedBox(height: 8),
            Text('Wi‑Fi détecté', style: Theme.of(context).textTheme.titleMedium),
            SelectableText(
              ssid == null || ssid.isEmpty
                  ? 'Inconnu (autorisez la localisation pour Auto)'
                  : ssid,
            ),
            const SizedBox(height: 4),
            Text(
              onHomeWifi
                  ? 'Réseau maison → LAN en priorité'
                  : 'Hors réseau maison / hotspot → Tailscale',
              style: Theme.of(context).textTheme.bodySmall,
            ),
            const SizedBox(height: 8),
            SwitchListTile(
              title: const Text('Forcer Tailscale'),
              subtitle: const Text(
                'Utile sur hotspot « steve » ou hors maison',
              ),
              value: _forceTailscale,
              onChanged: (value) {
                setState(() => _forceTailscale = value);
                _checkHealth();
              },
            ),
            if (_checking) const LinearProgressIndicator(),
            if (_result?.health.error != null) ...[
              const SizedBox(height: 12),
              Text(
                'Error: ${_result!.health.error}',
                style: const TextStyle(color: Colors.red),
              ),
            ],
            if (_result != null && _result!.candidatesTried.length > 1) ...[
              const SizedBox(height: 8),
              Text(
                'Essais: ${_result!.candidatesTried.join(' → ')}',
                style: Theme.of(context).textTheme.bodySmall,
              ),
            ],
            const SizedBox(height: 24),
            const Text('One Shadow. One Home. Everywhere.'),
            const SizedBox(height: 8),
            Text(
              'Maison: ${LumenConfig.connection.homeWifiSsids.join(', ')} · '
              'LAN: ${_manager.config.lanUrl} · '
              'Tailscale: ${_manager.config.tailscaleUrl}',
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _checking ? null : _checkHealth,
        icon: const Icon(Icons.health_and_safety),
        label: const Text('Check Lumen'),
      ),
    );
  }
}

class _StatusChip extends StatelessWidget {
  const _StatusChip({required this.label, required this.ok});

  final String label;
  final bool ok;

  @override
  Widget build(BuildContext context) {
    return Chip(
      avatar: Icon(
        ok ? Icons.check_circle : Icons.error_outline,
        color: ok ? Colors.green : Colors.orange,
        size: 18,
      ),
      label: Text(label),
    );
  }
}
