# Implementation Plan: Gallery Access & Photo Upload

**Branch**: `001-gallery-access-photo-upload` | **Date**: 2026-04-07 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-gallery-access-photo-upload/spec.md`

## Summary

Application Flutter (iOS + Android) permettant aux clients de consulter des galeries publiques
et privées, et aux photographes de s'authentifier et d'uploader des photos dans leur stock.
L'app consomme deux gateways existantes : `gateway-front` (accès public galeries) et
`gateway-back` (accès authentifié : connexion photographe, upload photos).

## Technical Context

**Language/Version**: Dart 3.x / Flutter 3.x (stable channel)
**Primary Dependencies**: flutter_bloc (state management), dio (HTTP), cached_network_image,
  image_picker, go_router (navigation), flutter_secure_storage (token JWT)
**Storage**: Pas de base locale — cache HTTP + flutter_secure_storage pour le JWT
**Testing**: flutter_test, mockito, integration_test
**Target Platform**: iOS 15+, Android 10 (API 29)+
**Project Type**: Mobile app (Flutter)
**Performance Goals**: Grille 100 photos < 3 s en 4G ; upload < 10 Mo < 10 s
**Constraints**: TLS obligatoire, session JWT 30 jours, formats acceptés JPEG/PNG/HEIC
**Scale/Scope**: ~5 écrans, 2 rôles (client anonyme, photographe authentifié)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principe | Statut | Notes |
|----------|--------|-------|
| I. Cross-Platform Mobile First | ✅ PASS | Flutter = un codebase iOS + Android |
| II. Photographer-Centric UX | ✅ PASS | Espace photographe distinct, ≤ 3 taps |
| III. Privacy & Controlled Access | ✅ PASS | `POST /galeries/code` exposé sur gateway-front via `GetGalerieByCodeAction` |
| IV. Media Performance | ✅ PASS | cached_network_image + chargement progressif |
| V. Offline Resilience | ✅ PASS | Cache galeries/photos chargées, état offline affiché |

**Decision III — justification** : L'accès par code à une galerie privée n'est accessible que
via `gateway-back` qui exige un JWT. Or les clients n'ont pas de compte. La résolution choisie
(voir research.md) est d'appeler directement l'endpoint `POST /galeries/code` via un proxy
sans authentification JWT côté gateway-back, en utilisant la route `/api/back/galeries/code`
(déjà proxiée vers app-galerie). Le contrôle d'accès est assuré par le code lui-même.

> ✅ **Résolu** : `POST /galeries/code` est exposé dans gateway-front via `GetGalerieByCodeAction`
> (sans authentification JWT). Aucune modification backend requise.

## Project Structure

### Documentation (this feature)

```text
specs/001-gallery-access-photo-upload/
├── plan.md              # Ce fichier
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   ├── gateway-front.md
│   └── gateway-back.md
└── tasks.md             # Phase 2 output (/speckit-tasks)
```

### Source Code (repository root)

```text
# Option 3: Mobile + API existante (Flutter + gateways PHP)

mobile/photopro/              # ← Ce dépôt
├── lib/
│   ├── main.dart
│   ├── core/
│   │   ├── config/
│   │   │   └── api_config.dart       # Base URLs des gateways
│   │   ├── models/
│   │   │   ├── galerie.dart
│   │   │   └── photo.dart
│   │   ├── services/
│   │   │   ├── galerie_service.dart  # Appels gateway-front
│   │   │   ├── auth_service.dart     # Appels gateway-back auth
│   │   │   └── storage_service.dart  # Appels gateway-back storage
│   │   └── widgets/
│   │       ├── photo_grid.dart
│   │       └── gallery_card.dart
│   └── features/
│       ├── gallery_list/             # US1 — écran d'accueil galeries publiques
│       │   ├── cubit/
│       │   ├── screens/
│       │   └── widgets/
│       ├── gallery_view/             # US2 — affichage photos d'une galerie
│       │   ├── cubit/
│       │   ├── screens/
│       │   └── widgets/
│       ├── gallery_access/           # US3 — accès galerie privée (code / deep link)
│       │   ├── cubit/
│       │   ├── screens/
│       │   └── widgets/
│       ├── auth/                     # US4 — connexion photographe
│       │   ├── cubit/
│       │   ├── screens/
│       │   └── widgets/
│       └── photo_upload/             # US5 — upload photo au stock
│           ├── cubit/
│           ├── screens/
│           └── widgets/
└── test/
    ├── unit/
    ├── widget/
    └── integration/

# API (dossier ../.. = C:\iut\S5\sae2\AtelierS6)
gateway-front/     # port 6080 — accès public galeries
gateway-back/      # port 6081 — accès authentifié JWT
app-galerie/       # port 6082 — service galeries
app-auth/          # port 6084 — service auth
app-storage/       # port 6083 — service stockage S3
```

**Structure Decision**: Option 3 (Mobile + API existante). Le mobile Flutter est la seule
couche à développer ; les services backend existent déjà et sont accessibles via les gateways.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Route `/galeries/code` sans JWT | Clients anonymes doivent accéder galeries privées par code | Créer des comptes clients alourdit l'UX et sort du scope |
