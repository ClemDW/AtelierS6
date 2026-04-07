<?php
declare(strict_types=1);

namespace photopro\infra\repositories;

use photopro\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use photopro\core\domain\exceptions\EmailAlreadyExistsException;
use Ramsey\Uuid\Uuid;

class PDOAuthRepository implements AuthRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findUserByEmail(string $email): ?array
    {
        $sql = 'SELECT id, email, password FROM users WHERE email = :email LIMIT 1';
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return [
                'id' => (string)$row['id'],
                'email' => (string)$row['email'],
                'password' => (string)$row['password'],
            ];
        } catch (\Throwable $e) {
            error_log('PDOAuthRepository error: ' . $e->getMessage());
            return null;
        }
    }

    public function createUser(string $email, string $hashedPassword): string
    {
        // Vérifier si l'email existe déjà
        if ($this->findUserByEmail($email) !== null) {
            throw new EmailAlreadyExistsException();
        }

        // Générer un ID unique (UUID v4)
        $userId = Uuid::uuid4()->toString();

        $sql = 'INSERT INTO users (id, email, password) VALUES (:id, :email, :password)';
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id' => $userId,
                'email' => $email,
                'password' => $hashedPassword,
            ]);
            
            return $userId;
        } catch (\PDOException $e) {
            // Gestion des erreurs de contraintes (duplicate key)
            if ($e->getCode() === '23000' || $e->getCode() === '23505') {
                throw new EmailAlreadyExistsException();
            }
            throw new \RuntimeException('Failed to create user: ' . $e->getMessage());
        }
    }

    public function saveRefreshToken(string $userId, string $refreshToken, \DateTime $expiresAt): void
    {
        $sql = 'INSERT INTO refresh_tokens (token, user_id, expires_at) VALUES (:token, :id, :expires_at)';
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id' => $userId,
                'token' => $refreshToken,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Failed to save refresh token: ' . $e->getMessage());
        }
    }

    public function isValidRefreshToken(string $userId, string $refreshToken): bool
    {
        $sql = 'SELECT 1 FROM refresh_tokens WHERE user_id = :id AND token = :token AND expires_at > NOW() LIMIT 1';
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id' => $userId,
                'token' => $refreshToken,
            ]);
            
            return (bool) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function findUserByRefreshToken(string $refreshToken): ?array
    {
        $sql = 'SELECT u.id, u.email, u.password 
                FROM users u
                INNER JOIN refresh_tokens rt ON u.id = rt.user_id
                WHERE rt.token = :token AND rt.expires_at > NOW() 
                LIMIT 1';
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['token' => $refreshToken]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) return null;

            return [
                'id' => (string)$row['id'],
                'email' => (string)$row['email'],
                'password' => (string)$row['password']
            ];
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function revokeRefreshToken(string $refreshToken): void
    {
        $sql = 'DELETE FROM refresh_tokens WHERE token = :token';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['token' => $refreshToken]);
        } catch (\PDOException $e) {
            // Log or ignore
        }
    }
}