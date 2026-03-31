-- Table Photo : On stocke la clé S3 (SeaweedFS)
CREATE TABLE photo (
    id UUID PRIMARY KEY,
    owner_id UUID NOT NULL, -- ID venant du service Identity
    mime_type VARCHAR(50) NOT NULL,
    taille_mo DECIMAL(10, 2) NOT NULL,
    nom_original VARCHAR(255) NOT NULL,
    cle_s3 VARCHAR(255) NOT NULL, -- Chemin dans SeaweedFS
    titre VARCHAR(255),
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table Galerie (Héritage Single Table)
CREATE TABLE galerie (
    id UUID PRIMARY KEY,
    photographe_id UUID NOT NULL, -- ID venant du service Identity
    type_galerie VARCHAR(20) NOT NULL, -- 'PUBLIQUE' ou 'PRIVEE'
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    est_publiee BOOLEAN DEFAULT FALSE,
    mode_mise_en_page VARCHAR(100),
    -- Champs spécifiques GaleriePrivee
    emails_client JSON, -- Liste des invités
    code_acces VARCHAR(100),
    url_acces_direct VARCHAR(255)
);

-- Table de jointure (Relation Photo-Galerie)
CREATE TABLE galerie_photo (
    galerie_id UUID REFERENCES galerie(id) ON DELETE CASCADE,
    photo_id UUID REFERENCES photo(id) ON DELETE CASCADE,
    PRIMARY KEY (galerie_id, photo_id)
);

-- Table Commentaire
CREATE TABLE commentaire (
    id SERIAL PRIMARY KEY,
    galerie_id UUID REFERENCES galerie(id) ON DELETE CASCADE,
    photo_id UUID REFERENCES photo(id) ON DELETE CASCADE,
    pseudo VARCHAR(100),
    contenu TEXT NOT NULL,
    date_post TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);