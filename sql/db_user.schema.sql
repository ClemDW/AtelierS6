CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Service d'Authentification / Identité
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(), 
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL -- Hashé 
);

CREATE TABLE refresh_tokens (
    token VARCHAR(255) PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);