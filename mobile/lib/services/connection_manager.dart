enum ConnectionMode {
  localhost,
  lan,
  tailscale,
  auto,
  cloud,
}

class ConnectionConfig {
  const ConnectionConfig({
    required this.mode,
    required this.localhostUrl,
    required this.lanUrl,
    required this.tailscaleUrl,
    required this.homeWifiSsids,
  });

  final ConnectionMode mode;
  final String localhostUrl;
  final String lanUrl;
  final String tailscaleUrl;
  final List<String> homeWifiSsids;

  /// SSIDs that must never be treated as home LAN (e.g. phone hotspot).
  static const hotspotSsids = {'steve'};
}

class ConnectionManager {
  ConnectionManager({required this.config});

  ConnectionConfig config;

  /// Returns true when [currentSsid] matches a configured home router SSID.
  static bool isOnHomeWifi({
    required String? currentSsid,
    required List<String> homeWifiSsids,
  }) {
    if (currentSsid == null || currentSsid.isEmpty) {
      return false;
    }

    final normalized = currentSsid.trim().toLowerCase();
    if (ConnectionConfig.hotspotSsids.contains(normalized)) {
      return false;
    }

    for (final home in homeWifiSsids) {
      if (home.trim().toLowerCase() == normalized) {
        return true;
      }
    }

    return false;
  }

  String resolveEndpoint({
    String? currentSsid,
    bool forceTailscale = false,
    bool lanReachable = true,
  }) {
    if (forceTailscale) {
      return config.tailscaleUrl;
    }

    final mode = config.mode == ConnectionMode.auto
        ? _autoMode(
            currentSsid: currentSsid,
            lanReachable: lanReachable,
          )
        : config.mode;

    return switch (mode) {
      ConnectionMode.localhost => config.localhostUrl,
      ConnectionMode.lan => config.lanUrl,
      ConnectionMode.tailscale => config.tailscaleUrl,
      ConnectionMode.auto => _autoEndpoint(currentSsid: currentSsid),
      ConnectionMode.cloud => config.tailscaleUrl,
    };
  }

  /// Ordered candidates for auto mode (LAN first only when on home Wi‑Fi).
  List<String> candidateEndpoints({
    String? currentSsid,
    bool forceTailscale = false,
  }) {
    if (forceTailscale) {
      return [config.tailscaleUrl];
    }

    return switch (config.mode) {
      ConnectionMode.localhost => [config.localhostUrl],
      ConnectionMode.lan => [config.lanUrl],
      ConnectionMode.tailscale => [config.tailscaleUrl],
      ConnectionMode.cloud => [config.tailscaleUrl],
      ConnectionMode.auto => _autoCandidates(currentSsid: currentSsid),
    };
  }

  ConnectionMode _autoMode({
    required String? currentSsid,
    required bool lanReachable,
  }) {
    if (isOnHomeWifi(
          currentSsid: currentSsid,
          homeWifiSsids: config.homeWifiSsids,
        ) &&
        lanReachable) {
      return ConnectionMode.lan;
    }

    return ConnectionMode.tailscale;
  }

  String _autoEndpoint({required String? currentSsid}) {
    final candidates = _autoCandidates(currentSsid: currentSsid);
    return candidates.first;
  }

  List<String> _autoCandidates({required String? currentSsid}) {
    if (isOnHomeWifi(
      currentSsid: currentSsid,
      homeWifiSsids: config.homeWifiSsids,
    )) {
      return [config.lanUrl, config.tailscaleUrl];
    }

    return [config.tailscaleUrl, config.lanUrl];
  }
}
