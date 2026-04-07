import 'package:dio/dio.dart';
import '../config/api_config.dart';
import 'secure_storage_service.dart';

class HttpClient {
  static Dio? _dioFront;
  static Dio? _dioBack;
  static final SecureStorageService _storage = SecureStorageService();

  static Dio get dioFront {
    _dioFront ??= Dio(
      BaseOptions(
        baseUrl: ApiConfig.gatewayFrontBaseUrl,
        connectTimeout: ApiConfig.connectTimeout,
        receiveTimeout: ApiConfig.receiveTimeout,
        headers: {'Content-Type': 'application/json'},
      ),
    );
    return _dioFront!;
  }

  static Dio get dioBack {
    if (_dioBack == null) {
      _dioBack = Dio(
        BaseOptions(
          baseUrl: ApiConfig.gatewayBackBaseUrl,
          connectTimeout: ApiConfig.connectTimeout,
          receiveTimeout: ApiConfig.receiveTimeout,
          headers: {'Content-Type': 'application/json'},
        ),
      );
      _dioBack!.interceptors.add(_JwtInterceptor(_storage, _dioBack!));
    }
    return _dioBack!;
  }
}

class _JwtInterceptor extends Interceptor {
  final SecureStorageService _storage;
  final Dio _dio;
  bool _isRefreshing = false;

  _JwtInterceptor(this._storage, this._dio);

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _storage.getToken();
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  @override
  Future<void> onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    if (err.response?.statusCode == 401 && !_isRefreshing) {
      _isRefreshing = true;
      try {
        final refreshToken = await _storage.getRefreshToken();
        if (refreshToken == null) {
          await _storage.clearAll();
          handler.next(err);
          return;
        }

        final response = await _dio.post(
          '/api/back/auth/refresh',
          data: {'refreshToken': refreshToken},
          options: Options(headers: {'Authorization': null}),
        );

        final data = (response.data['data'] ?? response.data) as Map<String, dynamic>;
        final newToken = data['access_token'] as String;
        final newRefresh = data['refresh_token'] as String;
        await _storage.saveToken(newToken);
        await _storage.saveRefreshToken(newRefresh);

        // Rejouer la requête originale
        err.requestOptions.headers['Authorization'] = 'Bearer $newToken';
        final retryResponse = await _dio.fetch(err.requestOptions);
        handler.resolve(retryResponse);
      } catch (_) {
        await _storage.clearAll();
        handler.next(err);
      } finally {
        _isRefreshing = false;
      }
    } else {
      handler.next(err);
    }
  }
}
