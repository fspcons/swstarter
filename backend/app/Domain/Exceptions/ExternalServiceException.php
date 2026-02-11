<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

class ExternalServiceException extends RuntimeException
{
    public static function swapiUnavailable(string $detail = ''): self
    {
        $message = 'The Star Wars API is currently unavailable.';
        if ($detail !== '') {
            $message .= " Detail: {$detail}";
        }

        return new self($message);
    }
}
