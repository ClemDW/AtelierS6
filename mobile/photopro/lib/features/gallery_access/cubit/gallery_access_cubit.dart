import 'package:dio/dio.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import '../../../core/models/galerie.dart';
import '../../../core/services/galerie_service.dart';

part 'gallery_access_state.dart';

class GalleryAccessCubit extends Cubit<GalleryAccessState> {
  final GalerieService _service;

  GalleryAccessCubit({GalerieService? service})
      : _service = service ?? GalerieService(),
        super(const GalleryAccessInitial());

  Future<void> submitCode(String code) async {
    if (code.trim().isEmpty) return;
    emit(const GalleryAccessLoading());
    try {
      final galerie = await _service.getGalerieByCode(code.trim());
      emit(GalleryAccessSuccess(galerie));
    } on DioException catch (e) {
      final status = e.response?.statusCode;
      if (status == 404) {
        emit(const GalleryAccessInvalidCode('Code invalide ou galerie introuvable.'));
      } else if (status == 400) {
        emit(const GalleryAccessInvalidCode('Code d\'accès manquant.'));
      } else {
        emit(GalleryAccessError(e.message ?? 'Erreur réseau.'));
      }
    } catch (e) {
      emit(GalleryAccessError(e.toString()));
    }
  }
}
