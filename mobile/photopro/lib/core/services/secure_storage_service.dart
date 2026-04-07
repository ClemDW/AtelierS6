import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorageService {
  static const _storage = FlutterSecureStorage();

  static const _keyToken = 'jwt_token';
  static const _keyRefresh = 'jwt_refresh';
  static const _keyPhotographeId = 'photographe_id';
  static const _keyEmail = 'photographe_email';

  Future<void> saveToken(String token) =>
      _storage.write(key: _keyToken, value: token);

  Future<String?> getToken() => _storage.read(key: _keyToken);

  Future<void> saveRefreshToken(String token) =>
      _storage.write(key: _keyRefresh, value: token);

  Future<String?> getRefreshToken() => _storage.read(key: _keyRefresh);

  Future<void> savePhotographeId(String id) =>
      _storage.write(key: _keyPhotographeId, value: id);

  Future<String?> getPhotographeId() => _storage.read(key: _keyPhotographeId);

  Future<void> saveEmail(String email) =>
      _storage.write(key: _keyEmail, value: email);

  Future<String?> getEmail() => _storage.read(key: _keyEmail);

  Future<void> clearAll() => _storage.deleteAll();
}
