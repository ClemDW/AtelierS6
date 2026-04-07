# Research: Gallery Access & Photo Upload

**Feature**: 001-gallery-access-photo-upload
**Date**: 2026-04-07

---

## R1 — Framework mobile : Flutter

**Decision**: Flutter 3.x (Dart 3.x, stable channel)
**Rationale**: Demande explicite de l'équipe. Flutter cible iOS + Android depuis un seul
codebase, satisfait le Principe I de la constitution. Les widgets Material/Cupertino permettent
une UI premium (Principe II). Performance native via compilation AOT.
**Alternatives considered**: React Native (écarté — déjà prévu dans la constitution mais
remplacé explicitement par l'utilisateur), Kotlin Multiplatform (trop jeune pour la UI).

---

## R2 — State management : flutter_bloc (Cubit)

**Decision**: flutter_bloc avec Cubit (simplifié vs BLoC complet)
**Rationale**: Pattern établi dans l'écosystème Flutter, séparation claire UI/logique,
testabilité élevée. Cubit est suffisant pour les 5 fonctionnalités de cette feature.
**Alternatives considered**: Provider (trop léger pour gérer états multiples), Riverpod
(pertinent mais ajout de complexité non justifié ici), GetX (anti-pattern).

---

## R3 — Client HTTP : dio

**Decision**: dio 5.x
**Rationale**: Intercepteurs pour injecter le JWT Bearer automatiquement, gestion du refresh
token, support multipart/form-data pour l'upload de photos, timeout configurables.
**Alternatives considered**: http (package officiel, trop basique — pas d'intercepteurs).

---

## R4 — Accès galerie privée par code (résolution du point III)

**Decision**: Appel `POST /api/back/galeries/code` (gateway-back) sans JWT pour les clients

**Analyse des routes existantes** :
- `gateway-front` : expose `GET /galeries` et `GET /galeries/{id}` uniquement — pas de route
  pour l'accès par code.
- `gateway-back` : expose `POST /api/back/galeries[/{path}]` proxié vers app-galerie, MAIS
  derrière le middleware `JwtAuthMiddleware` — impossible pour un client sans compte.
- `app-galerie` : `POST /galeries/code` existe et retourne la galerie si le code est valide.

**Résolution** : ✅ `POST /galeries/code` est exposé dans `gateway-front/config/routes.php`
via `GetGalerieByCodeAction` — sans authentification JWT, accès contrôlé par le code lui-même.
Aucune modification backend requise. L'app appelle directement `POST /galeries/code` sur
gateway-front (port 6080).

---

## R5 — Upload photo : multipart/form-data

**Decision**: `POST /api/back/photos/upload/{photographeId}` avec multipart/form-data
**Rationale**: Route existante dans gateway-back, proxiée vers app-storage. L'upload
utilise `image_picker` pour sélectionner l'image depuis la galerie de l'appareil, puis `dio`
pour l'envoi multipart. Formats validés côté serveur (JPEG, PNG, HEIC).
**Alternatives considered**: Base64 (trop lourd en bande passante), pré-signature S3 (non
exposée par les gateways actuelles).

---

## R6 — Authentification photographe : JWT avec refresh

**Decision**: JWT Bearer stocké dans `flutter_secure_storage`, refresh via
`POST /api/back/auth/refresh`, expiration 30 jours.
**Rationale**: Le JWT est émis par gateway-back via `/api/back/auth/signin`. Il doit être
stocké de façon sécurisée (Keychain iOS / Keystore Android). L'intercepteur dio détecte
les 401 et appelle automatiquement le refresh avant de rejouer la requête.
**Alternatives considered**: Stockage SharedPreferences (non sécurisé, écarté).

---

## R7 — Navigation : go_router

**Decision**: go_router 13.x avec deep link support
**Rationale**: Supporte les deep links (`photopro://gallery/{id}`) pour l'accès direct à une
galerie privée via lien partagé (US3). Intégration native avec Flutter Navigator 2.0.
**Alternatives considered**: auto_route (génération de code, overhead), Navigator 1.0 (pas de
deep link natif).

---

## R8 — Cache images : cached_network_image

**Decision**: cached_network_image 3.x
**Rationale**: Cache disque automatique des images téléchargées, placeholder pendant le
chargement, gestion d'erreur avec fallback. Satisfait le Principe IV (performance media)
et le Principe V (offline : photos déjà vues disponibles sans réseau).
**Alternatives considered**: flutter_cache_manager seul (moins intégré à l'UI).

---

## R9 — Architecture des gateways (résumé pour le mobile)

| Gateway | Port | Rôle | Auth requise |
|---------|------|------|-------------|
| gateway-front | 6080 | Lecture galeries publiques | Non |
| gateway-back | 6081 | Auth photographe, upload, galeries privées | JWT Bearer |

**Endpoints utilisés par l'app** :

| Feature | Méthode | Endpoint | Gateway |
|---------|---------|----------|---------|
| US1 — Liste galeries publiques | GET | `/galeries` | front:6080 |
| US2 — Afficher galerie | GET | `/galeries/{id}` | front:6080 |
| US3 — Accès galerie par code | POST | `/galeries/code` | front:6080* |
| US3 — Accès galerie par deep link | GET | `/galeries/{id}` | front:6080 |
| US4 — Connexion photographe | POST | `/api/back/auth/signin` | back:6081 |
| US4 — Refresh token | POST | `/api/back/auth/refresh` | back:6081 |
| US5 — Upload photo | POST | `/api/back/photos/upload/{id}` | back:6081 |

*Route à ajouter dans gateway-front (Option A recommandée — voir R4)
