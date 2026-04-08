# Contract: Gateway Front (port 6080)

**Base URL**: `http://localhost:6080` (dev) / TLS en production
**Auth**: Aucune — endpoints publics
**Feature**: 001-gallery-access-photo-upload

---

## GET /galeries

Liste toutes les galeries publiques.

**Request**
```
GET /galeries
```

**Response 200**
```json
[
  {
    "id": "string (uuid)",
    "photographeId": "string (uuid)",
    "type": "string",
    "titre": "string",
    "description": "string",
    "dateCreation": "YYYY-MM-DD",
    "datePublication": "YYYY-MM-DD",
    "isPublic": true,
    "mise_en_page": "string",
    "email_clients": [],
    "code_acces": "",
    "url": "string (url image couverture)",
    "photos": []
  }
]
```

**Response 500**
```json
{ "error": "Erreur lors de la communication avec le service galerie", "details": "string" }
```

**Utilisé par** : US1 (écran d'accueil)

---

## GET /galeries/{id}

Retourne le détail d'une galerie avec ses photos.

**Request**
```
GET /galeries/{id}
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| id | string (uuid) | Identifiant de la galerie |

**Response 200** — même structure que le tableau ci-dessus mais objet unique, avec `photos[]`
rempli.

**Response 404**
```json
{ "error": "Galerie non trouvée" }
```

**Utilisé par** : US2 (vue galerie), US3 (deep link)

---

## POST /galeries/code *(à ajouter — Option A recommandée)*

Retourne une galerie privée si le code d'accès est valide.

> **Note** : Cette route n'existe pas encore dans gateway-front. Elle doit être ajoutée en
> exposant l'endpoint `POST /galeries/code` du service app-galerie via un nouveau proxy public.

**Request**
```
POST /galeries/code
Content-Type: application/json

{ "code": "string" }
```

| Champ | Type | Requis | Description |
|-------|------|--------|-------------|
| code | string | oui | Code d'accès alphanumérique |

**Response 200** — objet Galerie complet (voir GET /galeries/{id})

**Response 400**
```json
{ "error": "Code d'accès manquant" }
```

**Response 404**
```json
{ "error": "Galerie introuvable ou code invalide" }
```

**Utilisé par** : US3 (accès galerie privée par code)
