import 'dart:convert';

import 'package:http/http.dart' as http;

class LumenHealthResult {
  const LumenHealthResult({
    required this.reachable,
    required this.endpoint,
    this.status,
    this.error,
  });

  final bool reachable;
  final String endpoint;
  final String? status;
  final String? error;
}

class LumenHealthService {
  LumenHealthService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  Future<LumenHealthResult> check(String baseUrl) async {
    final endpoint = baseUrl.endsWith('/')
        ? '${baseUrl}health'
        : '$baseUrl/health';

    try {
      final response = await _client
          .get(Uri.parse(endpoint))
          .timeout(const Duration(seconds: 8));

      if (response.statusCode != 200) {
        return LumenHealthResult(
          reachable: false,
          endpoint: endpoint,
          error: 'HTTP ${response.statusCode}',
        );
      }

      final decoded = jsonDecode(response.body);
      final status = decoded is Map ? decoded['status']?.toString() : null;

      return LumenHealthResult(
        reachable: status == 'ok',
        endpoint: endpoint,
        status: status,
      );
    } catch (error) {
      return LumenHealthResult(
        reachable: false,
        endpoint: endpoint,
        error: error.toString(),
      );
    }
  }
}
