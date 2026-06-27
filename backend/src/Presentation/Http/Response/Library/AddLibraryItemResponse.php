<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Library;

use App\Application\Library\DTO\AddLibraryItemResult;
use DateTimeInterface;

final readonly class AddLibraryItemResponse
{
    public function __construct(
        public string $id,
        public string $type,
        public string $title,
        public string $createdAt,
    ) {
    }

    public static function fromResult(AddLibraryItemResult $result): self
    {
        return new self(
            $result->libraryItemId->value,
            $result->type->value,
            $result->title->value,
            $result->createdAt->format(DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{id: string, type: string, title: string, createdAt: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'createdAt' => $this->createdAt,
        ];
    }
}
