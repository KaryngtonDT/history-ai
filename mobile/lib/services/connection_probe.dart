import 'connection_manager.dart';
import 'lumen_health_service.dart';

class ConnectionProbeResult {
  const ConnectionProbeResult({
    required this.health,
    required this.endpoint,
    required this.candidatesTried,
    this.currentSsid,
    this.onHomeWifi = false,
    this.forceTailscale = false,
  });

  final LumenHealthResult health;
  final String endpoint;
  final List<String> candidatesTried;
  final String? currentSsid;
  final bool onHomeWifi;
  final bool forceTailscale;
}

class ConnectionProbe {
  ConnectionProbe({
    required ConnectionManager manager,
    LumenHealthService? healthService,
  })  : _manager = manager,
        _healthService = healthService ?? LumenHealthService();

  final ConnectionManager _manager;
  final LumenHealthService _healthService;

  Future<ConnectionProbeResult> check({
    String? currentSsid,
    bool forceTailscale = false,
  }) async {
    final onHomeWifi = ConnectionManager.isOnHomeWifi(
      currentSsid: currentSsid,
      homeWifiSsids: _manager.config.homeWifiSsids,
    );

    final candidates = _manager.candidateEndpoints(
      currentSsid: currentSsid,
      forceTailscale: forceTailscale,
    );

    final tried = <String>[];
    LumenHealthResult? lastResult;

    for (final candidate in candidates) {
      tried.add(candidate);
      final result = await _healthService.check(candidate);
      lastResult = result;
      if (result.reachable) {
        return ConnectionProbeResult(
          health: result,
          endpoint: candidate,
          candidatesTried: tried,
          currentSsid: currentSsid,
          onHomeWifi: onHomeWifi,
          forceTailscale: forceTailscale,
        );
      }
    }

    return ConnectionProbeResult(
      health: lastResult ??
          LumenHealthResult(
            reachable: false,
            endpoint: candidates.first,
            error: 'No reachable endpoint',
          ),
      endpoint: candidates.first,
      candidatesTried: tried,
      currentSsid: currentSsid,
      onHomeWifi: onHomeWifi,
      forceTailscale: forceTailscale,
    );
  }
}
