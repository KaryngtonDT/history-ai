<?php

declare(strict_types=1);

namespace App\Application\Collection\Commands;

final readonly class CreateCollectionCommand
{
    public function __construct(
        public string $name,
        public string $description,
    ) {
    }
}
