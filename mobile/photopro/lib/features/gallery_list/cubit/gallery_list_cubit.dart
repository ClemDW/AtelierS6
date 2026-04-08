import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import '../../../core/models/galerie.dart';
import '../../../core/services/galerie_service.dart';

part 'gallery_list_state.dart';

class GalleryListCubit extends Cubit<GalleryListState> {
  final GalerieService _service;

  GalleryListCubit({GalerieService? service})
      : _service = service ?? GalerieService(),
        super(GalleryListInitial());

  Future<void> loadGaleries() async {
    emit(GalleryListLoading());
    try {
      final galeries = await _service.getGaleries();
      emit(GalleryListLoaded(galeries));
    } catch (e) {
      emit(GalleryListError(e.toString()));
    }
  }
}
