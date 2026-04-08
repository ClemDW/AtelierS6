import 'package:equatable/equatable.dart';

class AuthResult extends Equatable {
  final String token;
  final String refreshToken;
  final String photographeId;
  final String email;

  const AuthResult({
    required this.token,
    required this.refreshToken,
    required this.photographeId,
    required this.email,
  });

  factory AuthResult.fromJson(Map<String, dynamic> json) {
    final profile = json['profile'] as Map<String, dynamic>? ?? {};
    return AuthResult(
      token: (json['access_token'] ?? json['token'] ?? '') as String,
      refreshToken: (json['refresh_token'] ?? json['refreshToken'] ?? '') as String,
      photographeId: (profile['id'] ?? json['photographeId'] ?? '') as String,
      email: (profile['email'] ?? json['email'] ?? '') as String,
    );
  }

  @override
  List<Object?> get props => [token, refreshToken, photographeId, email];
}
