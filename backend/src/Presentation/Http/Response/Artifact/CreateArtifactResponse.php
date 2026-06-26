<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Artifact;

use App\Application\Artifact\DTO\CreateArtifactResult;
use DateTimeInterface;

final readonly class CreateArtifactResponse
{
    public function __construct(
        public string $id,
        public string $type,
        public string $createdAt,
    ) {
    }

    public static function fromResult(CreateArtifactResult $result): self
    {
        return new self(
            $result->artifactId->value,
            $result->type->value,
            $result->createdAt->format(DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{id: string, type: string, createdAt: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'createdAt' => $this->createdAt,
        ];
    }
}
