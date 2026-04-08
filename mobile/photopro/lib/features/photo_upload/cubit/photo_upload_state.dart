part of 'photo_upload_cubit.dart';

abstract class PhotoUploadState extends Equatable {
  const PhotoUploadState();
  @override
  List<Object?> get props => [];
}

class PhotoUploadIdle extends PhotoUploadState {
  const PhotoUploadIdle();
}

class PhotoUploadReady extends PhotoUploadState {
  final File file;
  const PhotoUploadReady(this.file);
  @override
  List<Object?> get props => [file.path];
}

class PhotoUploadUploading extends PhotoUploadState {
  final double progress;
  const PhotoUploadUploading(this.progress);
  @override
  List<Object?> get props => [progress];
}

class PhotoUploadSuccess extends PhotoUploadState {
  final Photo photo;
  const PhotoUploadSuccess(this.photo);
  @override
  List<Object?> get props => [photo];
}

class PhotoUploadFormatError extends PhotoUploadState {
  final String message;
  const PhotoUploadFormatError(this.message);
  @override
  List<Object?> get props => [message];
}

class PhotoUploadError extends PhotoUploadState {
  final String message;
  const PhotoUploadError(this.message);
  @override
  List<Object?> get props => [message];
}
