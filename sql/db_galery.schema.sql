CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 0. Table Photographe
CREATE TABLE IF NOT EXISTS photographe (
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
CREATE TABLE IF NOT EXISTS photo (
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
CREATE TABLE IF NOT EXISTS galerie (
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
CREATE TABLE IF NOT EXISTS galerie_photo (
    galerie_id UUID REFERENCES galerie(id) ON DELETE CASCADE,
    photo_id UUID REFERENCES photo(id) ON DELETE CASCADE,
    PRIMARY KEY (galerie_id, photo_id)
);

-- 4. Table Invitation (Version épurée)
CREATE TABLE IF NOT EXISTS invitation (
    id INT PRIMARY KEY,
    galerie_id UUID NOT NULL REFERENCES galerie(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    UNIQUE(galerie_id, email)
);

-- 5. Table Commentaire
CREATE TABLE IF NOT EXISTS commentaire (
    id INT PRIMARY KEY,
    galerie_id UUID NOT NULL REFERENCES galerie(id) ON DELETE CASCADE,
    photo_id UUID NOT NULL REFERENCES photo(id) ON DELETE CASCADE,
    pseudo VARCHAR(100) NOT NULL,
    contenu TEXT NOT NULL,
    date_post TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Suppression des données existantes (pour tester de zéro)
DELETE FROM commentaire;
DELETE FROM invitation;
DELETE FROM galerie_photo;
DELETE FROM photo;
DELETE FROM galerie;
DELETE FROM photographe;

-- 1. Ajout d'un photographe pour tester (l'ID doit idéalement matcher un compte auth)
INSERT INTO photographe (id, nom, pseudo, description, email_contact, date_creation)
VALUES 
('11111111-1111-1111-1111-111111111111', 'John Doe Photographie', 'johndoe', 'Photographe professionnel.', 'contact@johndoe.com', '2026-01-01 10:00:00'),
('22222222-2222-2222-2222-222222222222', 'Alice Paysage', 'alicepics', 'Passionnée par la nature.', 'alice@pics.com', '2026-02-15 14:30:00');

-- 2. Ajout de quelques photos dans le système
INSERT INTO photo (id, owner_id, mime_type, taille_mo, nom_original, cle_s3, titre)
VALUES 
('33333333-3333-3333-3333-333333333331', '11111111-1111-1111-1111-111111111111', 'image/jpeg', 2.5, 'mariage2.jpg', 'https://picsum.photos/400/200?random=1', 'La pièce montée'),
('33333333-3333-3333-3333-333333333332', '11111111-1111-1111-1111-111111111111', 'image/jpeg', 3.1, 'stade5.jpg', 'https://picsum.photos/400/200?random=2', 'Le but décisif'),
('33333333-3333-3333-3333-333333333333', '22222222-2222-2222-2222-222222222222', 'image/png', 5.0, 'montagne1.png', 'https://picsum.photos/400/200?random=3', 'Mont Blanc'),
('33333333-3333-3333-3333-333333333334', '22222222-2222-2222-2222-222222222222', 'image/jpeg', 1.8, 'foret.jpg', 'https://picsum.photos/400/200?random=4', 'Forêt en automne');

-- 3. Ajout de galeries (Publiques et Privées)
INSERT INTO galerie (id, photographe_id, type_galerie, titre, description, date_publication, est_publiee, mode_mise_en_page)
VALUES 
('44444444-4444-4444-4444-444444444441', '11111111-1111-1111-1111-111111111111', 'public', 'Mariage au Château', 'Les photos du mariages.', CURRENT_TIMESTAMP, TRUE, 'mosaïque'),
('44444444-4444-4444-4444-444444444442', '22222222-2222-2222-2222-222222222222', 'public', 'Voyage en Islande', 'Un froid glacial mais magnifique.', NULL, FALSE, 'liste'),
('44444444-4444-4444-4444-444444444443', '11111111-1111-1111-1111-111111111111', 'privee', 'Tournoi de Foot U15', 'Photos des matchs', CURRENT_TIMESTAMP, TRUE, 'grille');

-- 4. Liaison Galeries <-> Photos
INSERT INTO galerie_photo (galerie_id, photo_id)
VALUES
('44444444-4444-4444-4444-444444444441', '33333333-3333-3333-3333-333333333331'),
('44444444-4444-4444-4444-444444444443', '33333333-3333-3333-3333-333333333332'),
('44444444-4444-4444-4444-444444444442', '33333333-3333-3333-3333-333333333333'),
('44444444-4444-4444-4444-444444444442', '33333333-3333-3333-3333-333333333334');
