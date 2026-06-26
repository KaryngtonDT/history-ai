<?php

declare(strict_types=1);

namespace App\Application\Content\Commands;

use App\Domain\Content\ContentSourceType;

final readonly class CreateContentCommand
{
    public function __construct(
        public string $title,
        public ContentSourceType $sourceType,
    ) {
    }
}
