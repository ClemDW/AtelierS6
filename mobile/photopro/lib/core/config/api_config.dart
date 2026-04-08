class ApiConfig {
  // Android emulator: 10.0.2.2 pointe vers localhost de la machine hôte
  // iOS simulator: utiliser localhost
  // Appareil physique: remplacer par l'IP de la machine hôte
  static const String gatewayFrontBaseUrl = 'http://192.168.52.166:6080';
  static const String gatewayBackBaseUrl = 'http://192.168.52.166:6081';

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
