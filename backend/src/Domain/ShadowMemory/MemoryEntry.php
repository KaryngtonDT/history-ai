<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

use App\Domain\ShadowMemory\Exception\InvalidShadowMemoryException;

final readonly class MemoryEntry
{
    /**
     * @param list<string> $concepts
     * @param list<string> $sources
     */
    public function __construct(
        private string $id,
        private \DateTimeImmutable $recordedAt,
        private MemoryCategory $category,
        private MemoryImportance $importance,
        private MemoryConfidence $confidence,
        private string $label,
        private string $detail,
        private ?string $videoId,
        private ?int $segmentIndex,
        private ?string $sessionId,
        private ?string $conversationId,
        private array $concepts,
        private array $sources,
    ) {
        if ('' === trim($label)) {
            throw new InvalidShadowMemoryException('Memory entry label cannot be empty.');
        }
    }

    /**
     * @param list<string> $concepts
     * @param list<string> $sources
     */
    public static function record(
        MemoryCategory $category,
        string $label,
        string $detail = '',
        MemoryImportance $importance = MemoryImportance::Normal,
        MemoryConfidence $confidence = MemoryConfidence::Medium,
        ?string $videoId = null,
        ?int $segmentIndex = null,
        ?string $sessionId = null,
        ?string $conversationId = null,
        array $concepts = [],
        array $sources = [],
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            new \DateTimeImmutable(),
            $category,
            $importance,
            $confidence,
            $label,
            $detail,
            $videoId,
            $segmentIndex,
            $sessionId,
            $conversationId,
            $concepts,
            $sources,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function recordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function category(): MemoryCategory
    {
        return $this->category;
    }

    public function importance(): MemoryImportance
    {
        return $this->importance;
    }

    public function confidence(): MemoryConfidence
    {
        return $this->confidence;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function videoId(): ?string
    {
        return $this->videoId;
    }

    public function segmentIndex(): ?int
    {
        return $this->segmentIndex;
    }

    public function sessionId(): ?string
    {
        return $this->sessionId;
    }

    public function conversationId(): ?string
    {
        return $this->conversationId;
    }

    /** @return list<string> */
    public function concepts(): array
    {
        return $this->concepts;
    }

    /** @return list<string> */
    public function sources(): array
    {
        return $this->sources;
    }
}
