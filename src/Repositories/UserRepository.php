<?php

namespace Repositories;

use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO users (username, name, created_at) VALUES (?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['username'],
            $data['name']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function usernameExists(string $username): bool
    {
        return $this->findByUsername($username) !== null;
    }

    public function getStats(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_reviews,
                COALESCE(AVG(score), 0) as avg_score
            FROM product_reviews 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: ['total_reviews' => 0, 'avg_score' => 0];
    }
}
