import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../cubit/gallery_list_cubit.dart';
import '../../../core/widgets/gallery_card.dart';
import '../../../core/widgets/photopro_logo.dart';
import '../../../core/services/secure_storage_service.dart';

class GalleryListScreen extends StatefulWidget {
  const GalleryListScreen({super.key});

  @override
  State<GalleryListScreen> createState() => _GalleryListScreenState();
}

class _GalleryListScreenState extends State<GalleryListScreen> with RouteAware {
  final _storage = SecureStorageService();
  bool _isPhotographer = false;

  @override
  void initState() {
    super.initState();
    _checkAuth();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    // Rafraîchir l'état auth au retour sur cet écran
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    final token = await _storage.getToken();
    if (mounted) {
      setState(() => _isPhotographer = token != null && token.isNotEmpty);
    }
  }

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (_) => GalleryListCubit()..loadGaleries(),
      child: Builder(
        builder: (context) => Scaffold(
          appBar: AppBar(
            title: const PhotoProLogo(fontSize: 22),
            actions: [
              IconButton(
                icon: const Icon(Icons.refresh),
                tooltip: 'Rafraîchir',
                onPressed: () =>
                    context.read<GalleryListCubit>().loadGaleries(),
              ),
              IconButton(
                icon: const Icon(Icons.lock_open),
                tooltip: 'Accéder à une galerie privée',
                onPressed: () => context.push('/gallery/access'),
              ),
              if (_isPhotographer)
                IconButton(
                  icon: const Icon(Icons.person),
                  tooltip: 'Mon espace',
                  onPressed: () async {
                    await context.push('/photographer/dashboard');
                    _checkAuth();
                  },
                )
              else
                IconButton(
                  icon: const Icon(Icons.person_outline),
                  tooltip: 'Connexion photographe',
                  onPressed: () async {
                    await context.push('/photographer/login');
                    _checkAuth();
                  },
                ),
            ],
          ),
          floatingActionButton: _isPhotographer
              ? FloatingActionButton.extended(
                  onPressed: () => context.push('/photographer/upload'),
                  icon: const Icon(Icons.add_photo_alternate),
                  label: const Text('Ajouter une photo'),
                )
              : null,
          body: BlocBuilder<GalleryListCubit, GalleryListState>(
            builder: (context, state) {
              if (state is GalleryListLoading || state is GalleryListInitial) {
                return const Center(child: CircularProgressIndicator());
              }
              if (state is GalleryListError) {
                return Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 48, color: Colors.red),
                      const SizedBox(height: 16),
                      Text(state.message, textAlign: TextAlign.center),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () =>
                            context.read<GalleryListCubit>().loadGaleries(),
                        child: const Text('Réessayer'),
                      ),
                    ],
                  ),
                );
              }
              if (state is GalleryListLoaded) {
                if (state.galeries.isEmpty) {
                  return const Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.photo_library_outlined, size: 64, color: Colors.grey),
                        SizedBox(height: 16),
                        Text(
                          'Aucune galerie disponible',
                          style: TextStyle(fontSize: 16, color: Colors.grey),
                        ),
                      ],
                    ),
                  );
                }
                return RefreshIndicator(
                  onRefresh: () =>
                      context.read<GalleryListCubit>().loadGaleries(),
                  child: ListView.builder(
                    padding: const EdgeInsets.all(12),
                    itemCount: state.galeries.length,
                    itemBuilder: (context, index) {
                      final galerie = state.galeries[index];
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: GalleryCard(
                          galerie: galerie,
                          onTap: () => context.push('/gallery/${galerie.id}'),
                        ),
                      );
                    },
                  ),
                );
              }
              return const SizedBox.shrink();
            },
          ),
        ),
      ),
    );
  }
}
