import 'package:dio/dio.dart';
import '../models/auth_result.dart';
import 'http_client.dart';
import 'secure_storage_service.dart';

class AuthService {
  final Dio _dio = HttpClient.dioBack;
  final SecureStorageService _storage = SecureStorageService();

  Future<AuthResult> signin(String email, String password) async {
    final response = await _dio.post(
      '/api/back/auth/signin',
      data: {'email': email, 'password': password},
      options: Options(headers: {'Authorization': null}),
    );
    final result = AuthResult.fromJson(response.data as Map<String, dynamic>);
    await _storage.saveToken(result.token);
    await _storage.saveRefreshToken(result.refreshToken);
    await _storage.savePhotographeId(result.photographeId);
    await _storage.saveEmail(result.email);
    return result;
  }

  Future<void> refresh() async {
    final refreshToken = await _storage.getRefreshToken();
    if (refreshToken == null) throw Exception('No refresh token');
    final response = await _dio.post(
      '/api/back/auth/refresh',
      data: {'refreshToken': refreshToken},
      options: Options(headers: {'Authorization': null}),
    );
    final data = (response.data['data'] ?? response.data) as Map<String, dynamic>;
    await _storage.saveToken(data['access_token'] as String);
    await _storage.saveRefreshToken(data['refresh_token'] as String);
  }

  Future<bool> isLoggedIn() async {
    final token = await _storage.getToken();
    return token != null && token.isNotEmpty;
  }

  Future<void> logout() async {
    await _storage.clearAll();
  }
}
