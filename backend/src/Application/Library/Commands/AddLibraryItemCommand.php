<?php

declare(strict_types=1);

namespace App\Application\Library\Commands;

use App\Domain\Library\LibraryItemType;

final readonly class AddLibraryItemCommand
{
    public function __construct(
        public string $contentId,
        public string $artifactId,
        public LibraryItemType $type,
        public string $title,
    ) {
    }
}
