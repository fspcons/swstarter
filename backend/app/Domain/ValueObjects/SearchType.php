<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

enum SearchType: string
{
    case People = 'people';
    case Films = 'films';
}
