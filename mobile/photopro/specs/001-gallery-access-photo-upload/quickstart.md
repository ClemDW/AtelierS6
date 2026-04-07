# Quickstart: Gallery Access & Photo Upload

**Feature**: 001-gallery-access-photo-upload
**Date**: 2026-04-07

Ce guide permet de valider que l'environnement est prêt et que les user stories
fonctionnent de bout en bout.

---

## Prérequis

### Backend (AtelierS6)

1. Depuis `C:\iut\S5\sae2\AtelierS6` :
   ```bash
   docker compose up -d
   ```
2. Vérifier que les services sont disponibles :
   - Gateway Front : `http://localhost:6080/galeries` → retourne `[]` ou une liste
   - Gateway Back : `http://localhost:6081/` → retourne `{"service":"gateway-back.photopro",...}`

### Flutter (mobile)

1. Flutter SDK installé (stable channel) :
   ```bash
   flutter doctor
   ```
2. Depuis `C:\iut\S5\sae2\AtelierS6\mobile\photopro` :
   ```bash
   flutter pub get
   ```
3. Configurer les URLs dans `lib/core/config/api_config.dart` :
   ```dart
   const String gatewayFrontBaseUrl = 'http://10.0.2.2:6080'; // Android emulator
   const String gatewayBackBaseUrl  = 'http://10.0.2.2:6081'; // Android emulator
   // iOS simulator : remplacer 10.0.2.2 par localhost
   ```

---

## Validation par user story

### US1 — Client voit les galeries publiques

```bash
# Vérifier l'endpoint backend
curl http://localhost:6080/galeries

# Lancer l'app et vérifier
flutter run
```

**Attendu** : L'écran d'accueil affiche les galeries publiques (ou un message "Aucune galerie"
si la base est vide).

---

### US2 — Client affiche une galerie

```bash
# Récupérer l'ID d'une galerie depuis US1, puis :
curl http://localhost:6080/galeries/<id>
```

**Attendu** : L'objet galerie contient `photos[]`. Dans l'app, appuyer sur une galerie
affiche la grille de photos.

---

### US3 — Client accède à une galerie privée

```bash
# Tester l'accès par code (gateway-front après ajout de la route)
curl -X POST http://localhost:6080/galeries/code \
  -H "Content-Type: application/json" \
  -d '{"code":"ABCD1234"}'

# Résultat attendu : objet galerie (200) ou erreur (404)
```

**Attendu dans l'app** : L'écran "Accéder à une galerie" accepte le code et ouvre la galerie.
Avec un code invalide : message d'erreur visible.

---

### US4 — Photographe se connecte

```bash
curl -X POST http://localhost:6081/api/back/auth/signin \
  -H "Content-Type: application/json" \
  -d '{"email":"photo@example.com","password":"secret"}'

# Résultat attendu : {"token":"...","refreshToken":"...","photographeId":"...","email":"..."}
```

**Attendu dans l'app** : L'écran de connexion redirige vers le tableau de bord du photographe
après connexion réussie. Identifiants incorrects → message d'erreur générique.

---

### US5 — Photographe upload une photo

```bash
# Requiert un token JWT valide depuis US4
curl -X POST http://localhost:6081/api/back/photos/upload/<photographeId> \
  -H "Authorization: Bearer <token>" \
  -F "photo=@/chemin/vers/image.jpg"

# Résultat attendu : objet Photo (201)
```

**Attendu dans l'app** : L'écran de stock affiche un bouton "Ajouter". Après sélection d'une
image, une barre de progression s'affiche. La photo apparaît dans le stock après upload.

---

## Vérification globale

Checklist de validation manuelle (un appareil iOS + un Android) :

- [ ] US1 : Galeries publiques visibles sans connexion
- [ ] US2 : Grille de photos s'affiche en < 3 s (4G simulée)
- [ ] US2 : Navigation plein écran entre photos par glissement
- [ ] US3 : Code valide → galerie privée accessible
- [ ] US3 : Code invalide → message d'erreur clair
- [ ] US3 : Deep link `photopro://gallery/{id}` ouvre la bonne galerie
- [ ] US4 : Connexion photographe → tableau de bord
- [ ] US4 : Identifiants incorrects → message générique (pas "email incorrect")
- [ ] US4 : Session maintenue après fermeture/réouverture de l'app
- [ ] US5 : Sélection photo depuis galerie appareil → upload avec progression
- [ ] US5 : Format non supporté → message avec formats acceptés
- [ ] US5 : Photo uploadée visible dans le stock après refresh
