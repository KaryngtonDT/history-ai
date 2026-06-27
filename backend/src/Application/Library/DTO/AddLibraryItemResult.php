<?php

declare(strict_types=1);

namespace App\Application\Library\DTO;

use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemTitle;
use App\Domain\Library\LibraryItemType;
use DateTimeImmutable;

final readonly class AddLibraryItemResult
{
    public function __construct(
        public LibraryItemId $libraryItemId,
        public LibraryItemType $type,
        public LibraryItemTitle $title,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
