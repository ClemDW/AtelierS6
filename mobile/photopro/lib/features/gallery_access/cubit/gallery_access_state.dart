part of 'gallery_access_cubit.dart';

abstract class GalleryAccessState extends Equatable {
  const GalleryAccessState();
  @override
  List<Object?> get props => [];
}

class GalleryAccessInitial extends GalleryAccessState {
  const GalleryAccessInitial();
}

class GalleryAccessLoading extends GalleryAccessState {
  const GalleryAccessLoading();
}

class GalleryAccessSuccess extends GalleryAccessState {
  final Galerie galerie;
  const GalleryAccessSuccess(this.galerie);
  @override
  List<Object?> get props => [galerie];
}

class GalleryAccessInvalidCode extends GalleryAccessState {
  final String message;
  const GalleryAccessInvalidCode(this.message);
  @override
  List<Object?> get props => [message];
}

class GalleryAccessError extends GalleryAccessState {
  final String message;
  const GalleryAccessError(this.message);
  @override
  List<Object?> get props => [message];
}
