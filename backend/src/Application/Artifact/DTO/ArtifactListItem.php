<?php

declare(strict_types=1);

namespace App\Application\Artifact\DTO;

use App\Domain\Artifact\Artifact;
use DateTimeInterface;

final readonly class ArtifactListItem
{
    public function __construct(
        public string $id,
        public string $contentId,
        public string $processingJobId,
        public string $type,
        public string $content,
        public string $createdAt,
    ) {
    }

    public static function fromDomain(Artifact $artifact): self
    {
        return new self(
            id: $artifact->id()->value,
            contentId: $artifact->contentId()->value,
            processingJobId: $artifact->processingJobId()->value,
            type: $artifact->type()->value,
            content: $artifact->content()->value(),
            createdAt: $artifact->createdAt()->format(DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     contentId: string,
     *     processingJobId: string,
     *     type: string,
     *     content: string,
     *     createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contentId' => $this->contentId,
            'processingJobId' => $this->processingJobId,
            'type' => $this->type,
            'content' => $this->content,
            'createdAt' => $this->createdAt,
        ];
    }
}
