<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use DateTimeImmutable;

final class Collection
{
    private function __construct(
        private readonly CollectionId $id,
        private readonly CollectionName $name,
        private readonly CollectionDescription $description,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        CollectionId $id,
        CollectionName $name,
        CollectionDescription $description,
    ): self {
        return new self(
            $id,
            $name,
            $description,
            new DateTimeImmutable(),
        );
    }

    /**
     * Rebuilds a Collection aggregate from persistence. Used by infrastructure only.
     */
    public static function reconstitute(
        CollectionId $id,
        CollectionName $name,
        CollectionDescription $description,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $name,
            $description,
            $createdAt,
        );
    }

    public function id(): CollectionId
    {
        return $this->id;
    }

    public function name(): CollectionName
    {
        return $this->name;
    }

    public function description(): CollectionDescription
    {
        return $this->description;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
