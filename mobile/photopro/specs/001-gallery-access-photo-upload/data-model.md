# Data Model: Gallery Access & Photo Upload

**Feature**: 001-gallery-access-photo-upload
**Date**: 2026-04-07
**Source**: Entités PHP backend (`Galerie.php`, `Photo.php`) + réponses API observées

---

## Entités Dart (modèles mobiles)

### Galerie

Représente une galerie photo appartenant à un photographe.

```dart
class Galerie {
  final String id;
  final String photographeId;
  final String type;
  final String titre;
  final String description;
  final String dateCreation;      // ISO 8601
  final String datePublication;   // ISO 8601
  final bool isPublic;
  final String miseEnPage;        // ex: "grid", "masonry"
  final List<String> emailClients;
  final String codeAcces;         // vide si galerie publique
  final String url;               // URL de couverture
  final List<Photo> photos;
}
```

**Validation** :
- `titre` non vide
- `isPublic = false` implique `codeAcces` non vide
- `photos` peut être vide (galerie sans photos)

**Transitions d'état** (côté affichage) :
- `loading` → `loaded` → `error`
- `isPublic = false` + `codeAcces` valide → accessible

---

### Photo

Représente une photo dans le stock d'un photographe ou dans une galerie.

```dart
class Photo {
  final String id;
  final String ownerId;           // photographeId
  final String mimeType;          // "image/jpeg", "image/png", "image/heic"
  final double tailleMo;
  final String nomOriginal;
  final String cleS3;             // clé de stockage S3
  final String titre;
  final String dateUpload;        // ISO 8601
}
```

**Validation** :
- `mimeType` ∈ {"image/jpeg", "image/png", "image/heic"}
- `tailleMo` ≤ 50.0
- `cleS3` non vide après upload réussi

---

### AuthResult

Résultat de la connexion photographe.

```dart
class AuthResult {
  final String token;             // JWT access token
  final String refreshToken;
  final String photographeId;
  final String email;
}
```

**Stockage** : `flutter_secure_storage` — clés `jwt_token`, `jwt_refresh`, `photographe_id`

---

### GalerieAccessResult

Résultat de l'accès à une galerie privée par code.

```dart
class GalerieAccessResult {
  final bool success;
  final Galerie? galerie;         // null si code invalide
  final String? errorMessage;
}
```

---

## Relations

```
Photographe (id)
  └─── possède ──► Galerie (photographeId)
                      └─── contient ──► Photo[]
```

---

## Mapping JSON → Dart

### Galerie (réponse `GET /galeries` ou `GET /galeries/{id}`)

```json
{
  "id": "uuid",
  "photographeId": "uuid",
  "type": "string",
  "titre": "string",
  "description": "string",
  "dateCreation": "2026-04-01",
  "datePublication": "2026-04-07",
  "isPublic": true,
  "mise_en_page": "grid",
  "email_clients": [],
  "code_acces": "",
  "url": "https://...",
  "photos": [...]
}
```

> Note : le champ backend est `mise_en_page` (snake_case) — mapper vers `miseEnPage` en Dart.
> Le champ `code_acces` est présent mais peut être vide ou masqué pour les galeries publiques.

### Photo (dans `galerie.photos[]`)

```json
{
  "id": "uuid",
  "ownerId": "uuid",
  "mimeType": "image/jpeg",
  "tailleMo": 2.4,
  "nomOriginal": "IMG_001.jpg",
  "cleS3": "bucket/path/uuid.jpg",
  "titre": "Portrait",
  "dateUpload": "2026-04-07T10:00:00"
}
```
