<?php

namespace Models;

/**
 * ProductReview Entity - Pure data representation
 * No database logic - handled by ProductReviewRepository
 */
class ProductReview
{
    public ?int $id = null;
    public int $product_id;
    public ?int $user_id = null;
    public string $name;
    public string $username;
    public int $score;
    public string $text;
    public ?string $created_at = null;
    

    
    public function getStarRating(): string
    {
        return str_repeat('⭐', $this->score);
    }
    
    public function isPositive(): bool
    {
        return $this->score >= 4;
    }
}
