import 'package:equatable/equatable.dart';
import '../config/api_config.dart';

class Photo extends Equatable {
  final String id;
  final String ownerId;
  final String mimeType;
  final double tailleMo;
  final String nomOriginal;
  final String cleS3;
  final String titre;
  final String dateUpload;

  const Photo({
    required this.id,
    required this.ownerId,
    required this.mimeType,
    required this.tailleMo,
    required this.nomOriginal,
    required this.cleS3,
    required this.titre,
    required this.dateUpload,
  });

  factory Photo.fromJson(Map<String, dynamic> json) {
    return Photo(
      id: (json['id'] ?? '') as String,
      ownerId: (json['owner_id'] ?? json['ownerId'] ?? '') as String,
      mimeType: (json['mime_type'] ?? json['mimeType'] ?? '') as String,
      tailleMo: ((json['taille_mo'] ?? json['tailleMo'] ?? 0) as num).toDouble(),
      nomOriginal: (json['nom_original'] ?? json['nomOriginal'] ?? '') as String,
      cleS3: (json['cle_s3'] ?? json['cleS3'] ?? '') as String,
      titre: (json['titre'] ?? '') as String,
      dateUpload: (json['date_upload'] ?? json['dateUpload'] ?? '') as String,
    );
  }

  String get imageUrl =>
      '${ApiConfig.gatewayBackBaseUrl}/api/back/storage/photos/$id';

  Map<String, dynamic> toJson() => {
        'id': id,
        'ownerId': ownerId,
        'mimeType': mimeType,
        'tailleMo': tailleMo,
        'nomOriginal': nomOriginal,
        'cleS3': cleS3,
        'titre': titre,
        'dateUpload': dateUpload,
      };

  @override
  List<Object?> get props =>
      [id, ownerId, mimeType, tailleMo, nomOriginal, cleS3, titre, dateUpload];
}
