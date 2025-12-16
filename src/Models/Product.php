<?php

namespace Models;

/**
 * Product Entity - Pure data representation
 * No database logic - handled by ProductRepository
 */
use Core ;
class Product  
{
    public ?int $id = null;
    public string $name;
    public float $price;
    public int $click = 0;
    public ?string $description = null;
    public ?string $created_at = null;
    
    // Data transformation methods only
    
    public function getFormattedPrice(): string
    {
        return '€' . number_format($this->price, 2);
    }
    
    public function __toString(): string
    {
        return $this->name;
    }
}
