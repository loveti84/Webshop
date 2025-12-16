<?php

namespace Core;

class ValidationException extends \Exception {}

class Validator
{
    private array $errors = [];
    private array $validatedData = [];


    public static function validateInt($value, string $fieldName, ?int $min = null, ?int $max = null, bool $required = true): int
    {
        if ($value === null || $value === '') {
            if ($required) {
                throw new ValidationException("{$fieldName} is verplicht");
            }
            return 0;
        }

        if (!is_numeric($value) || $value != (int)$value) {
            throw new ValidationException("{$fieldName} moet een geldig geheel getal zijn");
        }

        $int = (int)$value;

        if ($min !== null && $int < $min) {
            throw new ValidationException("{$fieldName} moet minimaal {$min} zijn");
        }

        if ($max !== null && $int > $max) {
            throw new ValidationException("{$fieldName} mag maximaal {$max} zijn");
        }

        return $int;
    }

    
    public static function validateFloat($value, string $fieldName, ?float $min = null, ?float $max = null, bool $required = true): float
    {
        if ($value === null || $value === '') {
            if ($required) {
                throw new ValidationException("{$fieldName} is verplicht");
            }
            return 0.0;
        }

        if (!is_numeric($value)) {
            throw new ValidationException("{$fieldName} moet een getal zijn");
        }

        $float = (float)$value;

        if ($min !== null && $float < $min) {
            throw new ValidationException("{$fieldName} moet minimaal {$min} zijn");
        }

        if ($max !== null && $float > $max) {
            throw new ValidationException("{$fieldName} mag maximaal {$max} zijn");
        }

        return $float;
    }


    public static function validateString($value, string $fieldName, int $maxLength = 0, int $minLength = 0, bool $required = true): string
    {
        if ($value === null || $value === '') {
            if ($required) {
                throw new ValidationException("{$fieldName} is verplicht");
            }
            return '';
        }

        if (!is_string($value)) {
            throw new ValidationException("{$fieldName} moet een tekst zijn");
        }

        $trimmed = trim($value);

        if ($required && empty($trimmed)) {
            throw new ValidationException("{$fieldName} mag niet leeg zijn");
        }

        if ($minLength > 0 && mb_strlen($trimmed) < $minLength) {
            throw new ValidationException("{$fieldName} moet minimaal {$minLength} tekens bevatten");
        }

        if ($maxLength > 0 && mb_strlen($trimmed) > $maxLength) {
            throw new ValidationException("{$fieldName} mag maximaal {$maxLength} tekens bevatten");
        }

        return $trimmed;
    }

    public function int($value, string $fieldName, ?int $min = null, ?int $max = null, bool $required = true): self
    {
        try {
            $validated = self::validateInt($value, $fieldName, $min, $max, $required);
            $this->validatedData[$fieldName] = $validated;
        } catch (ValidationException $e) {
            $this->errors[$fieldName][] = $e->getMessage();
        }

        return $this;
    }


    public function float($value, string $fieldName, ?float $min = null, ?float $max = null, bool $required = true): self
    {
        try {
            $validated = self::validateFloat($value, $fieldName, $min, $max, $required);
            $this->validatedData[$fieldName] = $validated;
        } catch (ValidationException $e) {
            $this->errors[$fieldName][] = $e->getMessage();
        }

        return $this;
    }


    public function string($value, string $fieldName, int $maxLength = 0, int $minLength = 0, bool $required = true): self
    {
        try {
            $validated = self::validateString($value, $fieldName, $maxLength, $minLength, $required);
            $this->validatedData[$fieldName] = $validated;
        } catch (ValidationException $e) {
            $this->errors[$fieldName][] = $e->getMessage();
        }

        return $this;
    }

    public function custom(string $fieldName, callable $validator, ?string $errorMessage = null): self
    {
        $value = $this->validatedData[$fieldName] ?? null;
        
        if (!$validator($value)) {
            $message = $errorMessage ?? "{$fieldName} is ongeldig";
            $this->errors[$fieldName][] = $message;
        }

        return $this;
    }


    public function passes(): bool
    {
        return empty($this->errors);
    }


    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function errorMessage(): string
    {
        $messages = [];
        foreach ($this->errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $messages[] = $error;
            }
        }
        return implode('; ', $messages);
    }

    public function validated(): array
    {
        return $this->validatedData;
    }


    public function get(string $fieldName)
    {
        return $this->validatedData[$fieldName] ?? null;
    }
}