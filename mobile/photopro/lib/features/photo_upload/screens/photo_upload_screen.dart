import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import '../cubit/photo_upload_cubit.dart';

class PhotoUploadScreen extends StatelessWidget {
  const PhotoUploadScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (_) => PhotoUploadCubit(),
      child: const _PhotoUploadView(),
    );
  }
}

class _PhotoUploadView extends StatelessWidget {
  const _PhotoUploadView();

  Future<void> _pickImage(BuildContext context) async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.gallery);
    if (picked != null && context.mounted) {
      final file = File(picked.path);
      context.read<PhotoUploadCubit>().photoSelected(file);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Ajouter une photo')),
      body: BlocBuilder<PhotoUploadCubit, PhotoUploadState>(
        builder: (context, state) {
          return Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _buildPreview(context, state),
                const SizedBox(height: 24),
                _buildStatus(state),
                const SizedBox(height: 16),
                _buildActions(context, state),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildPreview(BuildContext context, PhotoUploadState state) {
    File? file;
    if (state is PhotoUploadReady) file = state.file;
    if (state is PhotoUploadUploading) {
      return Container(
        height: 200,
        decoration: BoxDecoration(
          color: Colors.grey[200],
          borderRadius: BorderRadius.circular(8),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(value: state.progress),
            const SizedBox(height: 12),
            Text('${(state.progress * 100).toStringAsFixed(0)} %'),
          ],
        ),
      );
    }
    if (file != null) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(8),
        child: Image.file(file, height: 200, fit: BoxFit.cover),
      );
    }
    return Container(
      height: 200,
      decoration: BoxDecoration(
        color: Colors.grey[200],
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.grey[400]!),
      ),
      child: const Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.add_photo_alternate, size: 64, color: Colors.grey),
          SizedBox(height: 8),
          Text('Aucune photo sélectionnée', style: TextStyle(color: Colors.grey)),
        ],
      ),
    );
  }

  Widget _buildStatus(PhotoUploadState state) {
    if (state is PhotoUploadSuccess) {
      return const Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.check_circle, color: Colors.green),
          SizedBox(width: 8),
          Text('Photo ajoutée avec succès !',
              style: TextStyle(color: Colors.green)),
        ],
      );
    }
    if (state is PhotoUploadFormatError) {
      return Text(state.message,
          style: const TextStyle(color: Colors.red), textAlign: TextAlign.center);
    }
    if (state is PhotoUploadError) {
      return Text(state.message,
          style: const TextStyle(color: Colors.red), textAlign: TextAlign.center);
    }
    return const SizedBox.shrink();
  }

  Widget _buildActions(BuildContext context, PhotoUploadState state) {
    final isUploading = state is PhotoUploadUploading;
    final hasFile =
        state is PhotoUploadReady || state is PhotoUploadFormatError;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        OutlinedButton.icon(
          icon: const Icon(Icons.photo_library),
          label: const Text('Choisir une photo'),
          onPressed: isUploading ? null : () => _pickImage(context),
        ),
        if (hasFile) ...[
          const SizedBox(height: 12),
          ElevatedButton.icon(
            icon: const Icon(Icons.upload),
            label: const Text('Uploader'),
            onPressed: () {
              final file = state is PhotoUploadReady ? state.file : null;
              if (file != null) {
                context.read<PhotoUploadCubit>().upload(file);
              }
            },
          ),
        ],
        if (state is PhotoUploadSuccess) ...[
          const SizedBox(height: 12),
          OutlinedButton(
            onPressed: () => context.read<PhotoUploadCubit>().reset(),
            child: const Text('Ajouter une autre photo'),
          ),
        ],
      ],
    );
  }
}
