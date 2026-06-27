<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Collection;

use App\Application\Collection\DTO\AssignLibraryItemToCollectionResult;
use DateTimeInterface;

final readonly class AssignLibraryItemToCollectionResponse
{
    public function __construct(
        public string $id,
        public string $collectionId,
        public string $libraryItemId,
        public string $createdAt,
    ) {
    }

    public static function fromResult(AssignLibraryItemToCollectionResult $result): self
    {
        return new self(
            $result->collectionItemId->value,
            $result->collectionId->value,
            $result->libraryItemId->value,
            $result->createdAt->format(DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     collectionId: string,
     *     libraryItemId: string,
     *     createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'collectionId' => $this->collectionId,
            'libraryItemId' => $this->libraryItemId,
            'createdAt' => $this->createdAt,
        ];
    }
}
