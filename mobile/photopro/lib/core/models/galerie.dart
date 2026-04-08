import 'package:equatable/equatable.dart';
import 'photo.dart';
import '../config/api_config.dart';

class Galerie extends Equatable {
  final String id;
  final String photographeId;
  final String type;
  final String titre;
  final String description;
  final String dateCreation;
  final String datePublication;
  final bool isPublic;
  final String miseEnPage;
  final List<String> emailClients;
  final String codeAcces;
  final String url;
  final String? photoEnteteId;
  final List<Photo> photos;

  const Galerie({
    required this.id,
    required this.photographeId,
    required this.type,
    required this.titre,
    required this.description,
    required this.dateCreation,
    required this.datePublication,
    required this.isPublic,
    required this.miseEnPage,
    required this.emailClients,
    required this.codeAcces,
    required this.url,
    this.photoEnteteId,
    required this.photos,
  });

  String? get photoEnteteUrl => photoEnteteId != null
      ? '${ApiConfig.gatewayBackBaseUrl}/api/back/storage/photos/$photoEnteteId'
      : null;

  factory Galerie.fromJson(Map<String, dynamic> json) {
    return Galerie(
      id: (json['id'] ?? '') as String,
      photographeId: (json['photographeId'] ?? json['photographe_id'] ?? '') as String,
      type: (json['type'] ?? json['type_galerie'] ?? '') as String,
      titre: (json['titre'] ?? '') as String,
      description: (json['description'] ?? '') as String,
      dateCreation: (json['dateCreation'] ?? json['date_creation'] ?? '') as String,
      datePublication: (json['datePublication'] ?? json['date_publication'] ?? '') as String,
      isPublic: (json['isPublic'] ?? json['is_public'] ?? json['est_publiee'] ?? false) as bool,
      miseEnPage: (json['mise_en_page'] ?? 'grid') as String,
      emailClients: List<String>.from(json['email_clients'] as List? ?? []),
      codeAcces: (json['code_acces'] ?? '') as String,
      url: (json['url'] ?? '') as String,
      photoEnteteId: json['photo_entete_id'] as String?,
      photos: (json['photos'] as List? ?? [])
          .map((p) => Photo.fromJson(p as Map<String, dynamic>))
          .toList(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'photographeId': photographeId,
        'type': type,
        'titre': titre,
        'description': description,
        'dateCreation': dateCreation,
        'datePublication': datePublication,
        'isPublic': isPublic,
        'mise_en_page': miseEnPage,
        'email_clients': emailClients,
        'code_acces': codeAcces,
        'url': url,
        'photo_entete_id': photoEnteteId,
        'photos': photos.map((p) => p.toJson()).toList(),
      };

  @override
  List<Object?> get props => [
        id,
        photographeId,
        type,
        titre,
        description,
        dateCreation,
        datePublication,
        isPublic,
        miseEnPage,
        emailClients,
        codeAcces,
        url,
        photoEnteteId,
        photos,
      ];
}
