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
}

class ConnectionManager {
  ConnectionManager({required this.config});

  ConnectionConfig config;

  String resolveEndpoint({
    required bool isOnHomeWifi,
    bool lanReachable = true,
    bool tailscaleReachable = true,
  }) {
    final mode = config.mode == ConnectionMode.auto
        ? _autoMode(isOnHomeWifi: isOnHomeWifi, lanReachable: lanReachable)
        : config.mode;

    return switch (mode) {
      ConnectionMode.localhost => config.localhostUrl,
      ConnectionMode.lan => config.lanUrl,
      ConnectionMode.tailscale => config.tailscaleUrl,
      ConnectionMode.auto => lanReachable && isOnHomeWifi
          ? config.lanUrl
          : config.tailscaleUrl,
      ConnectionMode.cloud => config.tailscaleUrl,
    };
  }

  ConnectionMode _autoMode({
    required bool isOnHomeWifi,
    required bool lanReachable,
  }) {
    if (isOnHomeWifi &&
        lanReachable &&
        _matchesHomeWifi(isOnHomeWifi: isOnHomeWifi)) {
      return ConnectionMode.lan;
    }

    return ConnectionMode.tailscale;
  }

  bool _matchesHomeWifi({required bool isOnHomeWifi}) {
    return isOnHomeWifi && config.homeWifiSsids.isNotEmpty;
  }
}
