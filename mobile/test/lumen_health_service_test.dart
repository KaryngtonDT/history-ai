import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:shadow_mobile/services/lumen_health_service.dart';

void main() {
  test('check returns ok when health endpoint responds', () async {
    final client = MockClient((request) async {
      expect(request.url.path, '/health');
      return http.Response('{"status":"ok"}', 200);
    });

    final service = LumenHealthService(client: client);
    final result = await service.check('http://100.111.236.50:8000');

    expect(result.reachable, isTrue);
    expect(result.status, 'ok');
  });

  test('check reports failure on non-200', () async {
    final client = MockClient((request) async {
      return http.Response('error', 503);
    });

    final service = LumenHealthService(client: client);
    final result = await service.check('http://127.0.0.1:8000');

    expect(result.reachable, isFalse);
    expect(result.error, contains('503'));
  });
}
