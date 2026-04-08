import 'package:flutter_test/flutter_test.dart';
import 'package:photopro/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const PhotoProApp());
    expect(find.byType(PhotoProApp), findsOneWidget);
  });
}
