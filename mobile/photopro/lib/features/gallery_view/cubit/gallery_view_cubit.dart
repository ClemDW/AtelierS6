import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import '../../../core/models/galerie.dart';
import '../../../core/services/galerie_service.dart';

part 'gallery_view_state.dart';

class GalleryViewCubit extends Cubit<GalleryViewState> {
  final GalerieService _service;

  GalleryViewCubit({GalerieService? service})
      : _service = service ?? GalerieService(),
        super(GalleryViewLoading());

  Future<void> loadGalerie(String id) async {
    emit(GalleryViewLoading());
    try {
      final galerie = await _service.getGalerieById(id);
      emit(GalleryViewLoaded(galerie));
    } catch (e) {
      emit(GalleryViewError(e.toString()));
    }
  }
}
