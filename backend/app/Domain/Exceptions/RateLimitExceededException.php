<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

class RateLimitExceededException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('API rate limit exceeded. Please try again shortly.');
    }
}
