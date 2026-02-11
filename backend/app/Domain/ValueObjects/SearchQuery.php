<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final class SearchQuery
{
    public readonly string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Search query cannot be empty.');
        }
        if (strlen($trimmed) > 100) {
            throw new InvalidArgumentException('Search query cannot exceed 100 characters.');
        }
        $this->value = $trimmed;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
