import 'package:flutter/material.dart';
import 'core/router/app_router.dart';

void main() {
  runApp(const PhotoProApp());
}

class PhotoProApp extends StatelessWidget {
  const PhotoProApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'PhotoPro',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: const ColorScheme.dark(
          primary: Color(0xFF1565C0),
          onPrimary: Colors.white,
          secondary: Color(0xFF1E88E5),
          onSecondary: Colors.white,
          surface: Color(0xFF121212),
          onSurface: Colors.white,
          error: Color(0xFFCF6679),
          onError: Colors.black,
        ),
        scaffoldBackgroundColor: const Color(0xFF0A0A0A),
        useMaterial3: true,
        appBarTheme: const AppBarTheme(
          centerTitle: true,
          elevation: 0,
          backgroundColor: Color(0xFF0D1B2A),
          foregroundColor: Colors.white,
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            minimumSize: const Size.fromHeight(48),
            backgroundColor: const Color(0xFF1565C0),
            foregroundColor: Colors.white,
          ),
        ),
        outlinedButtonTheme: OutlinedButtonThemeData(
          style: OutlinedButton.styleFrom(
            minimumSize: const Size.fromHeight(48),
            foregroundColor: const Color(0xFF1E88E5),
            side: const BorderSide(color: Color(0xFF1E88E5)),
          ),
        ),
        inputDecorationTheme: const InputDecorationTheme(
          filled: true,
          fillColor: Color(0xFF1A1A2E),
          border: OutlineInputBorder(),
          enabledBorder: OutlineInputBorder(
            borderSide: BorderSide(color: Color(0xFF1565C0)),
          ),
          focusedBorder: OutlineInputBorder(
            borderSide: BorderSide(color: Color(0xFF1E88E5), width: 2),
          ),
          labelStyle: TextStyle(color: Color(0xFF90CAF9)),
        ),
        cardTheme: const CardThemeData(
          color: Color(0xFF0D1B2A),
          elevation: 2,
        ),
      ),
      routerConfig: appRouter,
    );
  }
}
