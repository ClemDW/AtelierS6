import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import '../models/galerie.dart';

class GalleryCard extends StatelessWidget {
  final Galerie galerie;
  final VoidCallback onTap;

  const GalleryCard({super.key, required this.galerie, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            AspectRatio(
              aspectRatio: 16 / 9,
              child: galerie.url.isNotEmpty
                  ? CachedNetworkImage(
                      imageUrl: galerie.url,
                      fit: BoxFit.cover,
                      placeholder: (context, url) => Container(
                        color: const Color(0xFF21262D),
                        child: const Center(child: CircularProgressIndicator()),
                      ),
                      errorWidget: (context, url, error) => Container(
                        color: const Color(0xFF21262D),
                        child: const Icon(Icons.broken_image, size: 40),
                      ),
                    )
                  : Container(
                      color: const Color(0xFF21262D),
                      child: const Icon(Icons.photo_library, size: 40),
                    ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    galerie.titre,
                    style: Theme.of(context).textTheme.titleMedium,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    galerie.photographeId,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: const Color(0xFF8B949E),
                        ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
