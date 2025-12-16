<?php

namespace Repositories;

use PDO;

class ProductReviewRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        
    }

    public function getByProduct(int $productId): array
    {
        $sql = "SELECT pr.*, u.username, u.name 
                FROM product_reviews pr
                LEFT JOIN users u ON pr.user_id = u.id
                WHERE pr.product_id = ?
                ORDER BY pr.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getAverageScore(int $productId): float
    {
        $sql = "SELECT COALESCE(AVG(score), 0) as avg_score 
                FROM product_reviews 
                WHERE product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        $result = $stmt->fetch();
        return $result ? (float)$result['avg_score'] : 0.0;
    }

    public function userHasReviewed(int $userId, int $productId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM product_reviews 
                WHERE user_id = ? AND product_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $productId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO product_reviews (product_id, user_id, score, text, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['product_id'],
            $data['user_id'],
            $data['score'],
            $data['text']
        ]);
        return (int)$this->db->lastInsertId();
    }
}
