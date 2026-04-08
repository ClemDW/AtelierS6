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
          primary: Color(0xFF4D9EFF),
          onPrimary: Colors.white,
          secondary: Color(0xFF4D9EFF),
          onSecondary: Colors.white,
          surface: Color(0xFF0D1117),
          onSurface: Colors.white,
          error: Color(0xFFCF6679),
          onError: Colors.black,
        ),
        scaffoldBackgroundColor: const Color(0xFF0D1117),
        useMaterial3: true,
        appBarTheme: const AppBarTheme(
          centerTitle: true,
          elevation: 0,
          backgroundColor: Color(0xFF0D1117),
          foregroundColor: Colors.white,
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            minimumSize: const Size.fromHeight(48),
            backgroundColor: const Color(0xFF4D9EFF),
            foregroundColor: Colors.white,
          ),
        ),
        outlinedButtonTheme: OutlinedButtonThemeData(
          style: OutlinedButton.styleFrom(
            minimumSize: const Size.fromHeight(48),
            foregroundColor: const Color(0xFF4D9EFF),
            side: const BorderSide(color: Color(0xFF4D9EFF)),
          ),
        ),
        inputDecorationTheme: const InputDecorationTheme(
          filled: true,
          fillColor: Color(0xFF161B22),
          border: OutlineInputBorder(),
          enabledBorder: OutlineInputBorder(
            borderSide: BorderSide(color: Color(0xFF30363D)),
          ),
          focusedBorder: OutlineInputBorder(
            borderSide: BorderSide(color: Color(0xFF4D9EFF), width: 2),
          ),
          labelStyle: TextStyle(color: Color(0xFF8B949E)),
        ),
        cardTheme: const CardThemeData(
          color: Color(0xFF161B22),
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.all(Radius.circular(8)),
            side: BorderSide(color: Color(0xFF30363D)),
          ),
        ),
        floatingActionButtonTheme: const FloatingActionButtonThemeData(
          backgroundColor: Color(0xFF4D9EFF),
          foregroundColor: Colors.white,
        ),
      ),
      routerConfig: appRouter,
    );
  }
}
