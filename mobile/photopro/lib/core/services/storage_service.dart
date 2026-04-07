import 'dart:io';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/photo.dart';
import 'http_client.dart';

class StorageService {
  final Dio _dio = HttpClient.dioBack;

  Future<Photo> uploadPhoto(
    String photographeId,
    File file, {
    void Function(double progress)? onProgress,
  }) async {
    final mimeType = _detectMimeType(file.path);
    if (!ApiConfig.supportedImageMimeTypes.contains(mimeType)) {
      throw UnsupportedFormatException(
        'Format non supporté : ${file.path.split('.').last.toUpperCase()}. '
        'Formats acceptés : JPEG, PNG, HEIC.',
      );
    }

    final fileName = file.path.split(Platform.pathSeparator).last;
    final formData = FormData.fromMap({
      'photo': await MultipartFile.fromFile(
        file.path,
        filename: fileName,
        contentType: DioMediaType.parse(mimeType),
      ),
    });

    final response = await _dio.post(
      '/api/back/photos/upload/$photographeId',
      data: formData,
      onSendProgress: (sent, total) {
        if (total > 0 && onProgress != null) {
          onProgress(sent / total);
        }
      },
    );

    return Photo.fromJson(response.data as Map<String, dynamic>);
  }

  String _detectMimeType(String path) {
    final ext = path.split('.').last.toLowerCase();
    switch (ext) {
      case 'jpg':
      case 'jpeg':
        return 'image/jpeg';
      case 'png':
        return 'image/png';
      case 'heic':
        return 'image/heic';
      default:
        return 'application/octet-stream';
    }
  }
}

class UnsupportedFormatException implements Exception {
  final String message;
  UnsupportedFormatException(this.message);
  @override
  String toString() => message;
}
