import 'package:flutter_test/flutter_test.dart';
import 'package:shadow_mobile/main.dart';

void main() {
  testWidgets('Shadow Mobile home renders', (tester) async {
    await tester.pumpWidget(const ShadowMobileApp());
    await tester.pump();

    expect(find.text('Shadow Mobile'), findsOneWidget);
    expect(find.textContaining('One Shadow'), findsOneWidget);
  });
}
