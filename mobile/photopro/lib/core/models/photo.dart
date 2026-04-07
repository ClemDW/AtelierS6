import 'package:equatable/equatable.dart';

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
      id: json['id'] as String,
      ownerId: json['ownerId'] as String,
      mimeType: json['mimeType'] as String,
      tailleMo: (json['tailleMo'] as num).toDouble(),
      nomOriginal: json['nomOriginal'] as String,
      cleS3: json['cleS3'] as String,
      titre: json['titre'] as String,
      dateUpload: json['dateUpload'] as String,
    );
  }

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
