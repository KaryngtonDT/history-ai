<?php

declare(strict_types=1);

namespace App\Application\Collection\DTO;

use App\Domain\Collection\Collection;

final readonly class CollectionListItem
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $createdAt,
    ) {
    }

    public static function fromDomain(Collection $collection): self
    {
        return new self(
            id: $collection->id()->value,
            name: $collection->name()->value,
            description: $collection->description()->value,
            createdAt: $collection->createdAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     description: string,
     *     createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdAt' => $this->createdAt,
        ];
    }
}
