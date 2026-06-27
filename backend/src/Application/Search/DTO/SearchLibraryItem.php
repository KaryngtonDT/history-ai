<?php

declare(strict_types=1);

namespace App\Application\Search\DTO;

use App\Domain\Library\LibraryItem;

final readonly class SearchLibraryItem
{
    public function __construct(
        public string $id,
        public string $contentId,
        public string $artifactId,
        public string $type,
        public string $title,
        public string $createdAt,
    ) {
    }

    public static function fromDomain(LibraryItem $item): self
    {
        return new self(
            id: $item->id()->value,
            contentId: $item->contentId()->value,
            artifactId: $item->artifactId()->value,
            type: $item->type()->value,
            title: $item->title()->value,
            createdAt: $item->createdAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     contentId: string,
     *     artifactId: string,
     *     type: string,
     *     title: string,
     *     createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contentId' => $this->contentId,
            'artifactId' => $this->artifactId,
            'type' => $this->type,
            'title' => $this->title,
            'createdAt' => $this->createdAt,
        ];
    }
}
