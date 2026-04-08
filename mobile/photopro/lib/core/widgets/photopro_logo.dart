import 'package:flutter/material.dart';

class PhotoProLogo extends StatelessWidget {
  final double fontSize;

  const PhotoProLogo({super.key, this.fontSize = 32});

  @override
  Widget build(BuildContext context) {
    return RichText(
      text: TextSpan(
        style: TextStyle(
          fontSize: fontSize,
          fontWeight: FontWeight.bold,
          letterSpacing: 0.5,
        ),
        children: const [
          TextSpan(text: 'Photo', style: TextStyle(color: Colors.white)),
          TextSpan(text: 'Pro', style: TextStyle(color: Color(0xFF4D9EFF))),
        ],
      ),
    );
  }
}
