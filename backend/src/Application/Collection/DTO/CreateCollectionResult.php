<?php

declare(strict_types=1);

namespace App\Application\Collection\DTO;

use App\Domain\Collection\CollectionDescription;
use App\Domain\Collection\CollectionId;
use App\Domain\Collection\CollectionName;
use DateTimeImmutable;

final readonly class CreateCollectionResult
{
    public function __construct(
        public CollectionId $collectionId,
        public CollectionName $name,
        public CollectionDescription $description,
        public DateTimeImmutable $createdAt,
    ) {
    }
}
