# Feature Specification: Gallery Access & Photo Upload

**Feature Branch**: `001-gallery-access-photo-upload`
**Created**: 2026-04-07
**Status**: Draft
**Input**: User description: "Le client doit pouvoir voir les galeries publics quand il arrive sur
l'application, il doit pouvoir accéder à une galerie privé en entrant le lien ou le code d'accés
de la galerie, quand le client doit pouvoir afficher une galerie. Le photographe doit pouvoir se
connecter à l'application et doit pouvoir ajouter une photo à son stock photo."

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Client voit les galeries publiques à l'arrivée (Priority: P1)

En ouvrant l'application sans compte ni connexion, un client voit immédiatement la liste des
galeries que des photographes ont marquées comme publiques. Il peut parcourir ces galeries
librement, sans aucune action d'identification préalable.

**Why this priority**: C'est le point d'entrée principal de l'application côté client. Sans cette
expérience initiale, l'app n'offre rien à un nouveau visiteur et ne démontre pas sa valeur.

**Independent Test**: Ouvrir l'app sans aucun compte — la liste des galeries publiques DOIT
s'afficher. Ce test est complet et délivrable indépendamment.

**Acceptance Scenarios**:

1. **Given** l'app est ouverte pour la première fois (aucun compte, aucune session),
   **When** l'écran d'accueil se charge,
   **Then** toutes les galeries marquées "publiques" par leurs photographes s'affichent,
   triées par date de publication décroissante.

2. **Given** aucune galerie publique n'existe,
   **When** l'écran d'accueil se charge,
   **Then** un message d'état vide ("Aucune galerie disponible") s'affiche à la place de la liste.

3. **Given** plusieurs galeries publiques existent,
   **When** le client fait défiler la liste,
   **Then** chaque entrée affiche au minimum : la photo de couverture, le nom de la galerie,
   et le nom du photographe.

---

### User Story 2 - Client affiche le contenu d'une galerie (Priority: P2)

Depuis la liste des galeries (publiques ou après avoir déverrouillé une privée), le client
sélectionne une galerie et peut parcourir toutes les photos qui la composent.

**Why this priority**: La consultation d'une galerie est l'action centrale du client. Sans
cette fonctionnalité, l'application n'a pas de valeur de consommation.

**Independent Test**: Sélectionner une galerie publique depuis l'accueil — toutes les photos
de cette galerie doivent s'afficher en grille, et chaque photo doit pouvoir être agrandie.

**Acceptance Scenarios**:

1. **Given** une galerie publique existe avec 10 photos,
   **When** le client appuie sur cette galerie,
   **Then** un écran affichant les 10 photos en grille s'ouvre en moins de 3 secondes.

2. **Given** la galerie est ouverte,
   **When** le client appuie sur une photo individuelle,
   **Then** la photo s'affiche en plein écran avec la possibilité de naviguer vers la suivante
   ou la précédente par glissement horizontal.

3. **Given** la galerie est ouverte,
   **When** le client fait défiler une grille de 100+ photos,
   **Then** les photos se chargent progressivement sans blocage de l'interface.

---

### User Story 3 - Client accède à une galerie privée via lien ou code (Priority: P3)

Un client reçoit (par email, SMS ou autre canal externe) soit un lien direct vers une galerie
privée, soit un code d'accès. Il peut utiliser l'un ou l'autre pour déverrouiller et consulter
cette galerie depuis l'application.

**Why this priority**: Le partage privé est la proposition de valeur principale du photographe
professionnel envers ses clients. C'est la feature qui différencie l'app d'une galerie publique
ordinaire.

**Independent Test**: Entrer un code d'accès valide dans l'écran prévu — la galerie privée
correspondante DOIT s'ouvrir. Tester aussi avec un code invalide pour vérifier le refus.

**Acceptance Scenarios**:

1. **Given** le client possède un code d'accès valide à une galerie privée,
   **When** il entre ce code dans le champ dédié de l'application,
   **Then** la galerie privée s'ouvre et il peut en parcourir les photos.

2. **Given** le client ouvre un lien partagé (deep link) pointant vers une galerie privée,
   **When** l'application se lance ou est déjà ouverte,
   **Then** la galerie privée correspondante s'affiche directement sans autre action.

3. **Given** le client entre un code d'accès invalide ou expiré,
   **When** il valide le formulaire,
   **Then** un message d'erreur explicite s'affiche et aucune galerie n'est accessible.

4. **Given** le lien partagé est expiré,
   **When** le client tente d'ouvrir ce lien,
   **Then** un message indique que l'accès n'est plus disponible et propose de saisir un code.

---

### User Story 4 - Photographe s'authentifie (Priority: P4)

Un photographe possédant un compte accède à l'application et se connecte avec ses identifiants.
Après connexion, il accède à un espace personnel distinct de l'expérience client.

**Why this priority**: L'authentification est le prérequis à toute action du photographe
(gestion des galeries, upload de photos). Sans elle, aucune feature photographe ne peut être
utilisée.

**Independent Test**: Se connecter avec des identifiants valides — l'espace photographe DOIT
s'afficher. Tester avec des identifiants invalides pour vérifier le refus.

**Acceptance Scenarios**:

1. **Given** un photographe possède un compte valide,
   **When** il entre son email et son mot de passe puis valide,
   **Then** il est redirigé vers son tableau de bord personnel.

2. **Given** un photographe entre des identifiants incorrects,
   **When** il valide le formulaire de connexion,
   **Then** un message d'erreur générique s'affiche (sans révéler si c'est l'email ou le
   mot de passe qui est erroné) et il reste sur l'écran de connexion.

3. **Given** un photographe est connecté,
   **When** il ferme et rouvre l'application,
   **Then** sa session est maintenue et il n'a pas à se reconnecter (dans la limite de
   la durée de session standard).

---

### User Story 5 - Photographe ajoute une photo à son stock (Priority: P5)

Depuis son espace personnel, le photographe peut importer une ou plusieurs photos depuis
la galerie de son appareil vers son stock de photos. Ces photos sont stockées et pourront
ensuite être ajoutées à des galeries partagées.

**Why this priority**: L'alimentation du stock de photos est l'action fondamentale du
photographe. Sans upload, il n'a aucun contenu à partager avec ses clients.

**Independent Test**: Sélectionner une photo depuis la galerie de l'appareil et confirmer
l'import — la photo DOIT apparaître dans le stock du photographe après l'opération.

**Acceptance Scenarios**:

1. **Given** le photographe est connecté et accède à son stock,
   **When** il appuie sur "Ajouter une photo" et sélectionne une image de son appareil,
   **Then** la photo est importée et apparaît dans son stock en moins de 10 secondes
   (pour une image < 10 Mo en conditions réseau normales).

2. **Given** le photographe sélectionne une photo,
   **When** l'upload est en cours,
   **Then** une barre de progression ou un indicateur d'activité est visible.

3. **Given** la connexion est interrompue pendant l'upload,
   **When** la connectivité est rétablie,
   **Then** l'upload reprend ou un message propose de réessayer.

4. **Given** le photographe tente d'uploader un fichier non supporté (ex. PDF, vidéo),
   **When** il sélectionne le fichier,
   **Then** un message d'erreur explicite indique les formats acceptés et l'opération est annulée.

---

### Edge Cases

- Que se passe-t-il si le client perd la connexion en milieu de chargement d'une galerie ?
- Que se passe-t-il si une galerie publique est rendue privée alors qu'un client la consulte ?
- Que se passe-t-il si le photographe tente d'uploader une photo de très grande taille (> 50 Mo) ?
- Que se passe-t-il si un lien de galerie privée est partagé publiquement et utilisé par
  de nombreuses personnes simultanément ?

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: L'application DOIT afficher la liste des galeries publiques sur l'écran d'accueil
  sans aucune authentification requise.
- **FR-002**: Chaque galerie en liste DOIT afficher au minimum : une image de couverture, un nom
  de galerie, et le nom du photographe.
- **FR-003**: Le client DOIT pouvoir ouvrir une galerie et voir toutes ses photos en grille.
- **FR-004**: Le client DOIT pouvoir agrandir une photo en plein écran et naviguer entre photos.
- **FR-005**: Le client DOIT pouvoir accéder à une galerie privée en saisissant un code d'accès.
- **FR-006**: Le client DOIT pouvoir accéder à une galerie privée en ouvrant un lien direct
  (deep link).
- **FR-007**: Un code ou lien d'accès invalide ou expiré DOIT être rejeté avec un message
  d'erreur explicite.
- **FR-008**: Le photographe DOIT pouvoir se connecter avec un email et un mot de passe.
- **FR-009**: La session du photographe DOIT être maintenue entre les ouvertures de l'app.
- **FR-010**: Des identifiants incorrects DOIT produire un message d'erreur générique
  (sans préciser lequel des deux champs est erroné).
- **FR-011**: Le photographe connecté DOIT pouvoir sélectionner une ou plusieurs photos depuis
  la galerie de son appareil et les importer dans son stock.
- **FR-012**: L'application DOIT afficher la progression d'un upload en cours.
- **FR-013**: L'application DOIT rejeter les formats de fichier non supportés avec un message
  indiquant les formats acceptés.
- **FR-014**: Les formats d'image acceptés DOIT inclure au minimum JPEG et PNG.

### Key Entities

- **Galerie**: Ensemble de photos appartenant à un photographe, avec un nom, une visibilité
  (publique/privée), une image de couverture, une date de publication, et optionnellement
  un code d'accès et une date d'expiration.
- **Photo**: Fichier image appartenant au stock d'un photographe ; possède une URL de stockage,
  des métadonnées (nom, taille, format, date d'import).
- **Photographe**: Utilisateur authentifié disposant d'un espace personnel, d'un stock de photos
  et de galeries à gérer.
- **Client**: Visiteur non authentifié (ou identifié par un code d'accès) qui consulte des
  galeries partagées.
- **Code d'accès**: Identifiant court (alphanumérique) lié à une galerie privée, avec une date
  d'expiration optionnelle.

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Un nouveau visiteur voit la liste des galeries publiques en moins de 3 secondes
  après l'ouverture de l'application (réseau 4G standard).
- **SC-002**: Une galerie de 100 photos s'affiche en grille en moins de 3 secondes après sélection.
- **SC-003**: 100 % des tentatives d'accès avec un code invalide sont rejetées avec un message
  d'erreur visible.
- **SC-004**: Un photographe peut se connecter et accéder à son espace en moins de 5 secondes
  après validation de ses identifiants.
- **SC-005**: Une photo de moins de 10 Mo est importée dans le stock du photographe en moins
  de 10 secondes en conditions réseau normales.
- **SC-006**: 0 % des galeries privées ne sont accessibles sans code valide ou lien valide.
- **SC-007**: Le taux de complétion de l'upload (sans erreur réseau) est de 100 % pour les
  formats supportés de moins de 50 Mo.

---

## Assumptions

- Les photographes sont créés via un processus d'inscription géré séparément (hors scope de
  cette feature) ; seule la connexion d'un compte existant est couverte ici.
- Les galeries et leurs statuts (public/privé) sont gérés par une feature distincte ;
  cette spec couvre uniquement la consultation et l'upload de photos au stock.
- Les liens de deep link vers les galeries privées suivent un format standard de l'application
  (ex. `photopro://gallery/<id>?code=<code>`).
- Les formats d'image supportés à l'upload sont JPEG, PNG, et HEIC (format natif iOS).
- La taille maximale d'une photo à l'upload est fixée à 50 Mo par fichier.
- Les sessions photographe ont une durée de vie de 30 jours avec renouvellement automatique
  à chaque utilisation.
- L'application dispose d'un backend et d'un service de stockage de fichiers déjà provisionnés
  (hors scope de cette feature).
- Le client n'a pas besoin de créer de compte pour consulter des galeries publiques ou privées
  (accès par code uniquement).
