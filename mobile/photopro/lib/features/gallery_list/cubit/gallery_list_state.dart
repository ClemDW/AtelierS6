part of 'gallery_list_cubit.dart';

abstract class GalleryListState extends Equatable {
  const GalleryListState();
  @override
  List<Object?> get props => [];
}

class GalleryListInitial extends GalleryListState {}

class GalleryListLoading extends GalleryListState {}

class GalleryListLoaded extends GalleryListState {
  final List<Galerie> galeries;
  const GalleryListLoaded(this.galeries);
  @override
  List<Object?> get props => [galeries];
}

class GalleryListError extends GalleryListState {
  final String message;
  const GalleryListError(this.message);
  @override
  List<Object?> get props => [message];
}
