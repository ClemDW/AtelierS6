-- Service d'Authentification / Identité
CREATE TABLE users (
    id UUID PRIMARY KEY, 
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Hashé 
    nom VARCHAR(255),
    role VARCHAR(50) DEFAULT 'photographe',
    refresh_token TEXT,
    token_expires_at TIMESTAMP
);