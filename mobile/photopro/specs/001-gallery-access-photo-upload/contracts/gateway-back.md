# Contract: Gateway Back (port 6081)

**Base URL**: `http://localhost:6081` (dev) / TLS en production
**Auth**: JWT Bearer Token (sauf routes d'authentification)
**Header**: `Authorization: Bearer <token>`
**Feature**: 001-gallery-access-photo-upload

---

## POST /api/back/auth/signin

Authentifie un photographe et retourne un JWT.

**Request**
```
POST /api/back/auth/signin
Content-Type: application/json

{
  "email": "string",
  "password": "string"
}
```

| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| email | string | oui | Email du compte photographe |
| password | string | oui | Mot de passe |

**Response 200**
```json
{
  "token": "string (JWT)",
  "refreshToken": "string",
  "photographeId": "string (uuid)",
  "email": "string"
}
```

**Response 401**
```json
{ "error": "Identifiants invalides" }
```

**Comportement mobile** : Le token JWT est stocké dans `flutter_secure_storage`.
Un message d'erreur générique est affiché (sans préciser email ou mot de passe).

**Utilisé par** : US4 (connexion photographe)

---

## POST /api/back/auth/refresh

Rafraîchit un token JWT expiré.

**Request**
```
POST /api/back/auth/refresh
Content-Type: application/json

{ "refreshToken": "string" }
```

**Response 200**
```json
{ "token": "string (nouveau JWT)", "refreshToken": "string" }
```

**Response 401** — refresh token invalide ou expiré → déconnexion forcée côté app

**Utilisé par** : intercepteur dio (automatique, transparent pour l'utilisateur)

---

## POST /api/back/photos/upload/{id}

Upload une photo dans le stock du photographe.

**Request**
```
POST /api/back/photos/upload/{id}
Authorization: Bearer <token>
Content-Type: multipart/form-data

photo=<binary>
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| id (path) | string (uuid) | ID du photographe (== sub du JWT) |
| photo (form) | file | Fichier image (JPEG, PNG, HEIC, max 50 Mo) |

**Response 201**
```json
{
  "id": "string (uuid)",
  "ownerId": "string (uuid)",
  "mimeType": "image/jpeg",
  "tailleMo": 2.4,
  "nomOriginal": "IMG_001.jpg",
  "cleS3": "bucket/path/uuid.jpg",
  "titre": "IMG_001",
  "dateUpload": "2026-04-07T10:00:00"
}
```

**Response 400**
```json
{ "error": "Format non supporté ou fichier trop volumineux" }
```

**Response 401** — JWT invalide ou expiré (intercepteur dio déclenche refresh automatique)

**Comportement mobile** :
- Sélection via `image_picker` (galerie appareil)
- Envoi multipart via `dio` avec affichage de progression
- En cas de 400 : affichage du message d'erreur avec formats acceptés

**Utilisé par** : US5 (upload photo au stock)

---

## GET /api/back/me

Retourne les infos du photographe connecté depuis le JWT.

**Request**
```
GET /api/back/me
Authorization: Bearer <token>
```

**Response 200**
```json
{
  "id": "string (uuid)",
  "email": "string",
  "role": "photographe"
}
```

**Utilisé par** : US4 (vérification session, récupération photographeId)
