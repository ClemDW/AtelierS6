part of 'gallery_view_cubit.dart';

abstract class GalleryViewState extends Equatable {
  const GalleryViewState();
  @override
  List<Object?> get props => [];
}

class GalleryViewLoading extends GalleryViewState {}

class GalleryViewLoaded extends GalleryViewState {
  final Galerie galerie;
  const GalleryViewLoaded(this.galerie);
  @override
  List<Object?> get props => [galerie];
}

class GalleryViewError extends GalleryViewState {
  final String message;
  const GalleryViewError(this.message);
  @override
  List<Object?> get props => [message];
}
