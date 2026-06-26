<?php

declare(strict_types=1);

namespace App\Application\Content\DTO;

use App\Domain\Content\Content;

final readonly class ContentListItem
{
    public function __construct(
        public string $id,
        public string $title,
        public string $sourceType,
        public string $status,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromDomain(Content $content): self
    {
        return new self(
            id: $content->id()->value,
            title: $content->title()->value,
            sourceType: $content->sourceType()->value,
            status: $content->status()->value,
            createdAt: $content->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $content->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     sourceType: string,
     *     status: string,
     *     createdAt: string,
     *     updatedAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'sourceType' => $this->sourceType,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
