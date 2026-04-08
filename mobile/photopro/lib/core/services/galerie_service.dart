import 'package:dio/dio.dart';
import '../models/galerie.dart';
import 'http_client.dart';

class GalerieService {
  final Dio _dio = HttpClient.dioFront;

  Future<List<Galerie>> getGaleries() async {
    final response = await _dio.get('/galeries');
    final data = response.data as List;
    return data
        .map((json) => Galerie.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<Galerie> getGalerieById(String id) async {
    final response = await _dio.get('/galeries/$id');
    return Galerie.fromJson(response.data as Map<String, dynamic>);
  }

  Future<Galerie> getGalerieByCode(String code) async {
    final response = await _dio.post(
      '/galeries/code',
      data: {'code': code},
    );
    return Galerie.fromJson(response.data as Map<String, dynamic>);
  }
}
