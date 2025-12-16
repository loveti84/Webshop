<?php

namespace Models;

/**
 * User Entity - Pure data representation
 * No database logic - handled by UserRepository
 */
class User
{
    public ?int $id = null;
    public string $username;
    public string $name;
    public ?string $created_at = null;
    
    // Data transformation methods only
    
    public function getDisplayName(): string
    {
        return $this->name . ' (@' . $this->username . ')';
    }
    
    public function __toString(): string
    {
        return $this->username;
    }
}
