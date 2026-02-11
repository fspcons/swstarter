<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

class EntityNotFoundException extends RuntimeException
{
    public static function person(string $id): self
    {
        return new self("Person with ID '{$id}' was not found.");
    }

    public static function film(string $id): self
    {
        return new self("Film with ID '{$id}' was not found.");
    }
}
