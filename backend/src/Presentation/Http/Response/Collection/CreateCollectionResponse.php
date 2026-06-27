<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Collection;

use App\Application\Collection\DTO\CreateCollectionResult;
use DateTimeInterface;

final readonly class CreateCollectionResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $createdAt,
    ) {
    }

    public static function fromResult(CreateCollectionResult $result): self
    {
        return new self(
            $result->collectionId->value,
            $result->name->value,
            $result->description->value,
            $result->createdAt->format(DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{id: string, name: string, description: string, createdAt: string}
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
