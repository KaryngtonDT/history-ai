import 'package:flutter/material.dart';
import 'services/connection_manager.dart';

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

  @override
  void initState() {
    super.initState();
    _manager = ConnectionManager(
      config: const ConnectionConfig(
        mode: ConnectionMode.auto,
        localhostUrl: 'http://127.0.0.1:8080',
        lanUrl: 'http://192.168.1.10:8080',
        tailscaleUrl: 'http://100.64.0.1:8080',
        homeWifiSsids: ['HomeWiFi'],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final endpoint = _manager.resolveEndpoint(isOnHomeWifi: false);

    return Scaffold(
      appBar: AppBar(title: const Text('Shadow Mobile')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Mode: ${_manager.config.mode.name}'),
            Text('Endpoint: $endpoint'),
            const SizedBox(height: 16),
            const Text('One Shadow. One Home. Everywhere.'),
          ],
        ),
      ),
    );
  }
}
