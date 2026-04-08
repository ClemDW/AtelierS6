---
description: "Task list for Gallery Access & Photo Upload"
---

# Tasks: Gallery Access & Photo Upload

**Input**: Design documents from `/specs/001-gallery-access-photo-upload/`
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, contracts/ ✅

**Tests**: Non demandés explicitement — tâches de test non incluses.

**Organization**: Tâches groupées par user story pour permettre une implémentation et
un test indépendants de chaque story.

## Format: `[ID] [P?] [Story?] Description`

- **[P]**: Peut tourner en parallèle (fichiers différents, pas de dépendances)
- **[Story]**: User story concernée (US1 à US5)
- Chemins relatifs depuis `mobile/photopro/`

## Path Conventions

- Mobile Flutter : `lib/`, `test/` à la racine du dépôt `mobile/photopro/`
- API (lecture seule) : `../../gateway-front/`, `../../gateway-back/` (deux niveaux au-dessus)

---

## Phase 1: Setup (Infrastructure partagée)

**Purpose**: Initialisation du projet Flutter et structure de base

- [x] T001 Vérifier que le projet Flutter est initialisé et ajouter les dépendances dans `pubspec.yaml` : `flutter_bloc`, `dio`, `cached_network_image`, `image_picker`, `go_router`, `flutter_secure_storage`, `equatable`
- [x] T002 [P] Créer `lib/core/config/api_config.dart` avec les constantes `gatewayFrontBaseUrl` (port 6080) et `gatewayBackBaseUrl` (port 6081)
- [x] T003 [P] Créer la structure de dossiers `lib/features/` avec les sous-dossiers `gallery_list/`, `gallery_view/`, `gallery_access/`, `auth/`, `photo_upload/` (chacun avec `cubit/`, `screens/`, `widgets/`)

---

## Phase 2: Foundational (Prérequis bloquants)

**Purpose**: Modèles de données, client HTTP et routeur partagés par toutes les user stories

**⚠️ CRITIQUE** : Aucune user story ne peut commencer avant la fin de cette phase

- [x] T004 Créer le modèle `lib/core/models/galerie.dart` avec les champs : `id`, `photographeId`, `type`, `titre`, `description`, `dateCreation`, `datePublication`, `isPublic`, `miseEnPage`, `emailClients`, `codeAcces`, `url`, `photos` — inclure `fromJson()` et `toJson()`
- [x] T005 [P] Créer le modèle `lib/core/models/photo.dart` avec les champs : `id`, `ownerId`, `mimeType`, `tailleMo`, `nomOriginal`, `cleS3`, `titre`, `dateUpload` — inclure `fromJson()` et `toJson()`
- [x] T006 [P] Créer le modèle `lib/core/models/auth_result.dart` avec les champs : `token`, `refreshToken`, `photographeId`, `email` — inclure `fromJson()`
- [x] T007 Créer le client HTTP partagé `lib/core/services/http_client.dart` avec dio : deux instances (`dioFront` sans auth, `dioBack` avec intercepteur JWT Bearer) et gestion des timeouts (connexion 10 s, réception 30 s)
- [x] T008 [P] Créer `lib/core/services/secure_storage_service.dart` : wrapper flutter_secure_storage exposant `saveToken()`, `getToken()`, `saveRefreshToken()`, `getRefreshToken()`, `getPhotographeId()`, `clearAll()`
- [x] T009 Créer `lib/core/router/app_router.dart` avec go_router : routes `/` (gallery list), `/gallery/:id` (gallery view), `/gallery/access` (code access), `/photographer/login`, `/photographer/dashboard`, `/photographer/upload` — configurer le scheme deep link `photopro`

**Checkpoint** : Fondations prêtes — les user stories peuvent démarrer en parallèle

---

## Phase 3: User Story 1 - Client voit les galeries publiques (Priority: P1) 🎯 MVP

**Goal**: Afficher la liste des galeries publiques à l'ouverture de l'app, sans connexion

**Independent Test**: Lancer l'app sans compte → la liste des galeries publiques s'affiche.
Si aucune galerie : message "Aucune galerie disponible".

### Implémentation User Story 1

- [x] T010 [P] [US1] Implémenter `GalerieService.getGaleries()` dans `lib/core/services/galerie_service.dart` : appel `GET /galeries` via `dioFront`, désérialisation en `List<Galerie>` depuis le JSON
- [x] T011 [P] [US1] Créer `GalleryListCubit` dans `lib/features/gallery_list/cubit/gallery_list_cubit.dart` : états `GalleryListInitial`, `GalleryListLoading`, `GalleryListLoaded(List<Galerie>)`, `GalleryListError(String)` — appel `getGaleries()` au démarrage
- [x] T012 [P] [US1] Créer le widget `GalleryCard` dans `lib/core/widgets/gallery_card.dart` : affiche image de couverture (`cached_network_image`), titre de la galerie, nom du photographe
- [x] T013 [US1] Créer `GalleryListScreen` dans `lib/features/gallery_list/screens/gallery_list_screen.dart` : `BlocBuilder` sur `GalleryListCubit`, `ListView` de `GalleryCard`, état vide ("Aucune galerie disponible"), état d'erreur avec message, état de chargement avec `CircularProgressIndicator`

**Checkpoint** : US1 complète et testable indépendamment — lancer l'app et voir la liste

---

## Phase 4: User Story 2 - Client affiche le contenu d'une galerie (Priority: P2)

**Goal**: Ouvrir une galerie et parcourir ses photos en grille, avec vue plein écran

**Independent Test**: Appuyer sur une galerie publique → grille de photos en < 3 s.
Appuyer sur une photo → vue plein écran avec navigation par glissement.

### Implémentation User Story 2

- [x] T014 [P] [US2] Implémenter `GalerieService.getGalerieById(String id)` dans `lib/core/services/galerie_service.dart` : appel `GET /galeries/{id}` via `dioFront`, retourne `Galerie` avec `photos[]` rempli
- [x] T015 [P] [US2] Créer le widget `PhotoGrid` dans `lib/core/widgets/photo_grid.dart` : `GridView.builder` avec `cached_network_image` (thumbnail), chargement progressif, adapté à 100+ photos sans blocage UI
- [x] T016 [P] [US2] Créer `GalleryViewCubit` dans `lib/features/gallery_view/cubit/gallery_view_cubit.dart` : états `Loading`, `Loaded(Galerie)`, `Error(String)` — appel `getGalerieById()` à l'initialisation
- [x] T017 [US2] Créer `GalleryViewScreen` dans `lib/features/gallery_view/screens/gallery_view_screen.dart` : `BlocBuilder` sur `GalleryViewCubit`, affiche `PhotoGrid`, gestion état chargement/erreur, navigation vers `PhotoDetailScreen` au tap sur une photo
- [x] T018 [US2] Créer `PhotoDetailScreen` dans `lib/features/gallery_view/screens/photo_detail_screen.dart` : affichage plein écran `cached_network_image`, `PageView` pour navigation entre photos par glissement horizontal, bouton retour

**Checkpoint** : US1 + US2 fonctionnelles indépendamment

---

## Phase 5: User Story 3 - Client accède à une galerie privée (Priority: P3)

**Goal**: Accéder à une galerie privée via code d'accès ou lien deep link

**Independent Test**: Saisir un code valide → galerie privée s'ouvre.
Code invalide → message d'erreur. Deep link `photopro://gallery/{id}` → galerie directe.

### Implémentation User Story 3

- [x] T019 [P] [US3] Implémenter `GalerieService.getGalerieByCode(String code)` dans `lib/core/services/galerie_service.dart` : appel `POST /galeries/code` via `dioFront` avec body `{"code": code}`, retourne `Galerie` ou lève une exception sur 400/404
- [x] T020 [P] [US3] Créer `GalleryAccessCubit` dans `lib/features/gallery_access/cubit/gallery_access_cubit.dart` : états `Initial`, `Loading`, `Success(Galerie)`, `InvalidCode(String)`, `Expired` — appel `getGalerieByCode()` sur soumission du formulaire
- [x] T021 [P] [US3] Créer `GalleryAccessScreen` dans `lib/features/gallery_access/screens/gallery_access_screen.dart` : champ texte code d'accès, bouton valider, `BlocListener` pour naviguer vers `GalleryViewScreen` en cas de succès, afficher message d'erreur en cas d'échec
- [x] T022 [US3] Configurer le handler deep link dans `lib/core/router/app_router.dart` : intercepter `photopro://gallery/{id}` et rediriger vers `GalleryViewScreen` avec l'id ; si galerie non trouvée, afficher message et proposer saisie du code

**Checkpoint** : US1 + US2 + US3 fonctionnelles indépendamment

---

## Phase 6: User Story 4 - Photographe s'authentifie (Priority: P4)

**Goal**: Connexion photographe avec JWT, session persistante entre ouvertures de l'app

**Independent Test**: Connexion avec identifiants valides → tableau de bord affiché.
Identifiants incorrects → message générique. Réouverture app → session maintenue.

### Implémentation User Story 4

- [x] T023 [P] [US4] Implémenter `AuthService.signin(String email, String password)` dans `lib/core/services/auth_service.dart` : appel `POST /api/back/auth/signin` via `dioBack` (sans auth header pour cette route), stockage du JWT + refreshToken via `SecureStorageService`, retourne `AuthResult`
- [x] T024 [P] [US4] Implémenter `AuthService.refresh()` dans `lib/core/services/auth_service.dart` : appel `POST /api/back/auth/refresh` avec le refreshToken stocké, mise à jour du JWT en secure storage
- [x] T025 [US4] Ajouter l'intercepteur JWT + auto-refresh à `dioBack` dans `lib/core/services/http_client.dart` : sur réponse 401, appeler `AuthService.refresh()` et rejouer la requête ; si refresh échoue, vider le storage et rediriger vers LoginScreen
- [x] T026 [P] [US4] Créer `AuthCubit` dans `lib/features/auth/cubit/auth_cubit.dart` : états `AuthInitial`, `AuthLoading`, `AuthAuthenticated(AuthResult)`, `AuthError(String)`, `AuthUnauthenticated` — vérifier le token au démarrage, appeler `signin()` sur soumission
- [x] T027 [P] [US4] Créer `LoginScreen` dans `lib/features/auth/screens/login_screen.dart` : champs email + mot de passe, bouton connexion, `BlocListener` pour navigation vers `PhotographerDashboardScreen` en cas de succès, message d'erreur générique en cas d'échec (sans préciser email ou mot de passe)
- [x] T028 [US4] Créer `PhotographerDashboardScreen` dans `lib/features/auth/screens/photographer_dashboard_screen.dart` : affiche le nom/email du photographe récupéré depuis `SecureStorageService`, bouton "Ajouter une photo" naviguant vers `PhotoUploadScreen`, bouton déconnexion (vider storage + rediriger vers home)

**Checkpoint** : US1 à US4 fonctionnelles indépendamment

---

## Phase 7: User Story 5 - Photographe ajoute une photo à son stock (Priority: P5)

**Goal**: Sélectionner une photo depuis l'appareil et l'uploader dans le stock du photographe

**Independent Test**: Sélectionner JPEG depuis galerie → upload avec barre de progression →
photo visible dans le stock. Format PDF → message d'erreur avec formats acceptés.

### Implémentation User Story 5

- [x] T029 [P] [US5] Implémenter `StorageService.uploadPhoto(String photographeId, File photo)` dans `lib/core/services/storage_service.dart` : appel `POST /api/back/photos/upload/{id}` via `dioBack` en multipart/form-data, exposer un `Stream<double>` de progression (0.0 → 1.0), retourne `Photo`
- [x] T030 [P] [US5] Créer `PhotoUploadCubit` dans `lib/features/photo_upload/cubit/photo_upload_cubit.dart` : états `Idle`, `Picking`, `Uploading(double progress)`, `Success(Photo)`, `FormatError(String)`, `UploadError(String)` — valider le format (JPEG/PNG/HEIC) avant l'appel, gérer la perte réseau avec message de retry
- [x] T031 [US5] Créer `PhotoUploadScreen` dans `lib/features/photo_upload/screens/photo_upload_screen.dart` : bouton "Choisir une photo" (appel `image_picker`), `BlocBuilder` affichant aperçu de l'image sélectionnée, barre `LinearProgressIndicator` pendant l'upload, message de succès ou d'erreur avec les formats acceptés (JPEG, PNG, HEIC)

**Checkpoint** : Les 5 user stories sont fonctionnelles et testables indépendamment

---

## Phase N: Polish & Préoccupations transversales

**Purpose**: Améliorations qualité applicables à toutes les user stories

- [ ] T032 [P] Ajouter la détection de connectivité réseau dans `lib/core/services/http_client.dart` et afficher une bannière "Hors ligne" dans `GalleryListScreen` et `GalleryViewScreen` (Principe V — Offline Resilience)
- [x] T033 [P] Ajouter des widgets d'état vide cohérents dans `lib/core/widgets/empty_state.dart` : utilisés dans `GalleryListScreen` (aucune galerie) et `GalleryViewScreen` (aucune photo)
- [x] T034 [P] Vérifier la conformité deep link sur iOS (`Info.plist` — scheme `photopro`) et Android (`AndroidManifest.xml` — intent-filter) dans les fichiers de configuration plateforme
- [ ] T035 Exécuter la validation du `quickstart.md` sur un appareil physique iOS ET Android : cocher chaque item de la checklist de validation

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Aucune — peut démarrer immédiatement
- **Foundational (Phase 2)**: Dépend de Phase 1 — **BLOQUE toutes les user stories**
- **US1 (Phase 3)**: Dépend de Foundational — indépendante des autres stories
- **US2 (Phase 4)**: Dépend de Foundational — peut démarrer en parallèle avec US1 après Foundational
- **US3 (Phase 5)**: Dépend de Foundational — peut démarrer en parallèle avec US1/US2
- **US4 (Phase 6)**: Dépend de Foundational — peut démarrer en parallèle avec US1/US2/US3
- **US5 (Phase 7)**: Dépend de US4 (nécessite un photographe connecté + l'intercepteur JWT de T025)
- **Polish (Phase N)**: Dépend de toutes les stories désirées

### User Story Dependencies

- **US1 (P1)**: Après Foundational — aucune dépendance inter-story
- **US2 (P2)**: Après Foundational — réutilise `GalerieService` de US1 (T010/T014 dans même fichier)
- **US3 (P3)**: Après Foundational — réutilise `GalerieService` et `GalleryViewScreen` de US2
- **US4 (P4)**: Après Foundational — indépendante de US1/US2/US3
- **US5 (P5)**: Après US4 (dépend de l'intercepteur JWT T025 et du `photographeId` en storage)

### Within Each User Story

- Modèles/Services (tâches [P]) → Cubit → Screen
- Services parallélisables entre eux (fichiers différents)
- Screen dépend du Cubit

### Parallel Opportunities

- T004, T005, T006 : modèles en parallèle (fichiers indépendants)
- T007, T008 : services fondamentaux en parallèle
- T010, T011, T012 : service + cubit + widget US1 en parallèle
- T014, T015, T016 : service + widget + cubit US2 en parallèle
- T019, T020, T021 : service + cubit + screen US3 en parallèle
- T023, T024, T026, T027 : services + cubit + screens US4 en parallèle (T025 dépend de T023/T024)
- T029, T030 : service + cubit US5 en parallèle (T031 dépend des deux)
- T032, T033, T034 : polish en parallèle

---

## Parallel Example: User Story 1

```bash
# Lancer en parallèle (fichiers différents, aucune dépendance mutuelle) :
Task: "GalerieService.getGaleries() dans lib/core/services/galerie_service.dart"   # T010
Task: "GalleryListCubit dans lib/features/gallery_list/cubit/gallery_list_cubit.dart"  # T011
Task: "GalleryCard dans lib/core/widgets/gallery_card.dart"                          # T012

# Puis (dépend des trois précédents) :
Task: "GalleryListScreen dans lib/features/gallery_list/screens/gallery_list_screen.dart"  # T013
```

---

## Implementation Strategy

### MVP First (User Story 1 uniquement)

1. Compléter Phase 1 : Setup
2. Compléter Phase 2 : Foundational (**CRITIQUE** — bloque tout)
3. Compléter Phase 3 : US1 (galeries publiques)
4. **STOP et VALIDER** : Ouvrir l'app, voir la liste des galeries → MVP livrable
5. Démo possible

### Incremental Delivery

1. Setup + Foundational → base prête
2. US1 → liste galeries publiques → **MVP client**
3. US2 → affichage photos → **MVP consommation galeries**
4. US3 → accès galeries privées → **différenciation photographe**
5. US4 → connexion photographe → **espace professionnel**
6. US5 → upload photo → **cycle complet photographe**
7. Polish → qualité production

### Parallel Team Strategy (2 développeurs)

Après completion de Foundational :
- **Dev A** : US1 → US2 → US3 (parcours client)
- **Dev B** : US4 → US5 (parcours photographe)

---

## Notes

- `[P]` = fichiers différents, pas de dépendances — peut être lancé en parallèle
- `[USx]` = traceabilité vers la user story correspondante dans spec.md
- Chaque checkpoint valide l'indépendance de la story avant de passer à la suivante
- US5 dépend de US4 : ne pas démarrer US5 avant que T025 (intercepteur JWT) soit complet
- Backend : `POST /galeries/code` est exposé sur gateway-front via `GetGalerieByCodeAction` ✅ — T019 peut être implémenté sans attendre
