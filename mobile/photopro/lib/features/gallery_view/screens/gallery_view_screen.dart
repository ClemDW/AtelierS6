import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../cubit/gallery_view_cubit.dart';
import '../../../core/widgets/photo_grid.dart';
import 'photo_detail_screen.dart';

class GalleryViewScreen extends StatelessWidget {
  final String galerieId;

  const GalleryViewScreen({super.key, required this.galerieId});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (_) => GalleryViewCubit()..loadGalerie(galerieId),
      child: BlocBuilder<GalleryViewCubit, GalleryViewState>(
        builder: (context, state) {
          if (state is GalleryViewLoading) {
            return Scaffold(
              appBar: AppBar(),
              body: const Center(child: CircularProgressIndicator()),
            );
          }
          if (state is GalleryViewError) {
            return Scaffold(
              appBar: AppBar(),
              body: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.error_outline, size: 48, color: Colors.red),
                    const SizedBox(height: 16),
                    Text(state.message, textAlign: TextAlign.center),
                    const SizedBox(height: 16),
                    ElevatedButton(
                      onPressed: () =>
                          context.read<GalleryViewCubit>().loadGalerie(galerieId),
                      child: const Text('Réessayer'),
                    ),
                  ],
                ),
              ),
            );
          }
          if (state is GalleryViewLoaded) {
            final galerie = state.galerie;
            return Scaffold(
              appBar: AppBar(title: Text(galerie.titre)),
              body: PhotoGrid(
                photos: galerie.photos,
                onPhotoTap: (index) {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => PhotoDetailScreen(
                        photos: galerie.photos,
                        initialIndex: index,
                      ),
                    ),
                  );
                },
              ),
            );
          }
          return const Scaffold(body: SizedBox.shrink());
        },
      ),
    );
  }
}
