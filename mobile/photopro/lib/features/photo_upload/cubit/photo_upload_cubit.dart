import 'dart:io';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:equatable/equatable.dart';
import '../../../core/models/photo.dart';
import '../../../core/services/secure_storage_service.dart';
import '../../../core/services/storage_service.dart';

part 'photo_upload_state.dart';

class PhotoUploadCubit extends Cubit<PhotoUploadState> {
  final StorageService _service;
  final SecureStorageService _storage;

  PhotoUploadCubit({StorageService? service, SecureStorageService? storage})
      : _service = service ?? StorageService(),
        _storage = storage ?? SecureStorageService(),
        super(const PhotoUploadIdle());

  void photoSelected(File file) {
    emit(PhotoUploadReady(file));
  }

  Future<void> upload(File file) async {
    emit(const PhotoUploadUploading(0.0));
    try {
      final photographeId = await _storage.getPhotographeId();
      if (photographeId == null) {
        emit(const PhotoUploadError('Session expirée. Veuillez vous reconnecter.'));
        return;
      }

      final photo = await _service.uploadPhoto(
        photographeId,
        file,
        onProgress: (p) => emit(PhotoUploadUploading(p)),
      );

      emit(PhotoUploadSuccess(photo));
    } on UnsupportedFormatException catch (e) {
      emit(PhotoUploadFormatError(e.message));
    } catch (e) {
      emit(PhotoUploadError(
        'Erreur lors de l\'upload. Vérifiez votre connexion et réessayez.',
      ));
    }
  }

  void reset() => emit(const PhotoUploadIdle());
}
