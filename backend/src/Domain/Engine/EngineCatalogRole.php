<?php

declare(strict_types=1);

namespace App\Domain\Engine;

enum EngineCatalogRole: string
{
    case Default = 'default';
    case Alternative1 = 'alternative_1';
    case Alternative2 = 'alternative_2';

    public function label(): string
    {
        return match ($this) {
            self::Default => 'Default',
            self::Alternative1 => 'Alternative 1',
            self::Alternative2 => 'Alternative 2',
        };
    }
}
