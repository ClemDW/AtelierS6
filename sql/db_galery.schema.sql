CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 0. Table Photographe
CREATE TABLE photographe (
    id UUID PRIMARY KEY, -- Même ID que l'utilisateur dans app-auth
    nom VARCHAR(255) NOT NULL,
    pseudo VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    email_contact VARCHAR(255),
    tel_contact VARCHAR(50),
    image_profil VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- 1. Table Photo (Stockage brut)
CREATE TABLE photo (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    owner_id UUID NOT NULL, 
    mime_type VARCHAR(50) NOT NULL, -- Type de média (JPG, PNG)
    taille_mo DECIMAL(10, 2) NOT NULL,
    nom_original VARCHAR(255) NOT NULL, 
    cle_s3 VARCHAR(255) NOT NULL, 
    titre VARCHAR(255), 
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Table Galerie (Avec date_publication)
CREATE TABLE galerie (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    photographe_id UUID NOT NULL, 
    type_galerie VARCHAR(20) NOT NULL, -- 'PUBLIQUE' ou 'PRIVEE'
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_publication TIMESTAMP,        -- Reste NULL tant que ce n'est pas publié
    est_publiee BOOLEAN DEFAULT FALSE,
    mode_mise_en_page VARCHAR(100),

    -- Pour les galeries privés
    code_acces VARCHAR(100),
    url_acces VARCHAR(255)
);

-- 3. Table de jointure (Relation Photo-Galerie avec ordre)
CREATE TABLE galerie_photo (
    galerie_id UUID REFERENCES galerie(id) ON DELETE CASCADE,
    photo_id UUID REFERENCES photo(id) ON DELETE CASCADE,
    PRIMARY KEY (galerie_id, photo_id)
);

-- 4. Table Invitation (Version épurée)
CREATE TABLE invitation (
    id INT PRIMARY KEY,
    galerie_id UUID NOT NULL REFERENCES galerie(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    UNIQUE(galerie_id, email)
);

-- 5. Table Commentaire
CREATE TABLE commentaire (
    id INT PRIMARY KEY,
    galerie_id UUID NOT NULL REFERENCES galerie(id) ON DELETE CASCADE,
    photo_id UUID NOT NULL REFERENCES photo(id) ON DELETE CASCADE,
    pseudo VARCHAR(100) NOT NULL,
    contenu TEXT NOT NULL,
    date_post TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);