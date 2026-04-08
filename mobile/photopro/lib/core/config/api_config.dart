class ApiConfig {
  // Android emulator: 10.0.2.2 pointe vers localhost de la machine hôte
  // iOS simulator: utiliser localhost
  // Appareil physique: remplacer par l'IP de la machine hôte
  static const String gatewayFrontBaseUrl = 'http://docketu.iutnc.univ-lorraine.fr:11201';
  static const String gatewayBackBaseUrl = 'http://docketu.iutnc.univ-lorraine.fr:11202';

  static const Duration connectTimeout = Duration(seconds: 10);
  static const Duration receiveTimeout = Duration(seconds: 30);

  static const List<String> supportedImageMimeTypes = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif',
  ];

  static const double maxUploadSizeMo = 50.0;
}
