import 'package:flutter/material.dart';

import 'config/lumen_config.dart';
import 'services/connection_manager.dart';
import 'services/lumen_health_service.dart';

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
  final LumenHealthService _healthService = LumenHealthService();

  LumenHealthResult? _health;
  bool _checking = false;
  bool _assumeHomeWifi = true;

  @override
  void initState() {
    super.initState();
    _manager = ConnectionManager(config: LumenConfig.connection);
    _checkHealth();
  }

  Future<void> _checkHealth() async {
    setState(() => _checking = true);

    final endpoint = _manager.resolveEndpoint(isOnHomeWifi: _assumeHomeWifi);
    final result = await _healthService.check(endpoint);

    if (!mounted) {
      return;
    }

    setState(() {
      _health = result;
      _checking = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final endpoint = _manager.resolveEndpoint(isOnHomeWifi: _assumeHomeWifi);
    final connected = _health?.reachable ?? false;

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
            _StatusChip(
              label: 'Mode: ${_manager.config.mode.name}',
              ok: true,
            ),
            const SizedBox(height: 8),
            _StatusChip(
              label: connected ? 'Server: ok' : 'Server: unavailable',
              ok: connected,
            ),
            const SizedBox(height: 16),
            Text('Endpoint', style: Theme.of(context).textTheme.titleMedium),
            SelectableText(endpoint),
            const SizedBox(height: 8),
            SwitchListTile(
              title: const Text('Simulate home Wi‑Fi (steve)'),
              subtitle: const Text('Off = Tailscale path (remote)'),
              value: _assumeHomeWifi,
              onChanged: (value) {
                setState(() => _assumeHomeWifi = value);
                _checkHealth();
              },
            ),
            if (_checking) const LinearProgressIndicator(),
            if (_health?.error != null) ...[
              const SizedBox(height: 12),
              Text('Error: ${_health!.error}', style: const TextStyle(color: Colors.red)),
            ],
            const SizedBox(height: 24),
            const Text('One Shadow. One Home. Everywhere.'),
            const SizedBox(height: 8),
            const Text('Tailscale: 100.111.236.50 · LAN: 192.168.43.194 · Wi‑Fi: steve'),
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
