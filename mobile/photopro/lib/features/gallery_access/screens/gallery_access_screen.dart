import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import '../cubit/gallery_access_cubit.dart';

class GalleryAccessScreen extends StatefulWidget {
  const GalleryAccessScreen({super.key});

  @override
  State<GalleryAccessScreen> createState() => _GalleryAccessScreenState();
}

class _GalleryAccessScreenState extends State<GalleryAccessScreen> {
  final _controller = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (_) => GalleryAccessCubit(),
      child: BlocListener<GalleryAccessCubit, GalleryAccessState>(
        listener: (context, state) {
          if (state is GalleryAccessSuccess) {
            context.push('/gallery/${state.galerie.id}');
          }
        },
        child: Scaffold(
          appBar: AppBar(title: const Text('Accéder à une galerie privée')),
          body: Padding(
            padding: const EdgeInsets.all(24),
            child: Form(
              key: _formKey,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.lock, size: 64, color: Colors.grey),
                  const SizedBox(height: 24),
                  const Text(
                    'Entrez le code d\'accès fourni par votre photographe',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 16),
                  ),
                  const SizedBox(height: 24),
                  TextFormField(
                    controller: _controller,
                    decoration: const InputDecoration(
                      labelText: 'Code d\'accès',
                      border: OutlineInputBorder(),
                      prefixIcon: Icon(Icons.vpn_key),
                    ),
                    textCapitalization: TextCapitalization.none,
                    validator: (v) =>
                        (v == null || v.trim().isEmpty) ? 'Veuillez saisir un code' : null,
                  ),
                  const SizedBox(height: 16),
                  BlocBuilder<GalleryAccessCubit, GalleryAccessState>(
                    builder: (context, state) {
                      if (state is GalleryAccessInvalidCode ||
                          state is GalleryAccessError) {
                        final msg = state is GalleryAccessInvalidCode
                            ? state.message
                            : (state as GalleryAccessError).message;
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: Text(
                            msg,
                            style: const TextStyle(color: Colors.red),
                            textAlign: TextAlign.center,
                          ),
                        );
                      }
                      return const SizedBox.shrink();
                    },
                  ),
                  BlocBuilder<GalleryAccessCubit, GalleryAccessState>(
                    builder: (context, state) {
                      final isLoading = state is GalleryAccessLoading;
                      return SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: isLoading
                              ? null
                              : () {
                                  if (_formKey.currentState!.validate()) {
                                    context
                                        .read<GalleryAccessCubit>()
                                        .submitCode(_controller.text);
                                  }
                                },
                          child: isLoading
                              ? const SizedBox(
                                  height: 20,
                                  width: 20,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : const Text('Accéder'),
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
