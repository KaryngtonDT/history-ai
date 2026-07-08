<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

final readonly class RuntimeResolveContext
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public ?string $language = null,
        public ?int $durationSeconds = null,
        public ?string $hardwareProfile = null,
        public ?string $consumer = null,
        public ?string $preferredEngineId = null,
        public ?string $processingMode = null,
        public array $metadata = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            language: is_string($data['language'] ?? null) ? $data['language'] : null,
            durationSeconds: is_numeric($data['durationSeconds'] ?? null)
                ? (int) $data['durationSeconds']
                : null,
            hardwareProfile: is_string($data['hardwareProfile'] ?? null) ? $data['hardwareProfile'] : null,
            consumer: is_string($data['consumer'] ?? null) ? $data['consumer'] : null,
            preferredEngineId: is_string($data['preferredEngineId'] ?? null) ? $data['preferredEngineId'] : null,
            processingMode: is_string($data['processingMode'] ?? null) ? $data['processingMode'] : null,
            metadata: is_array($data['metadata'] ?? null) ? $data['metadata'] : [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'language' => $this->language,
            'durationSeconds' => $this->durationSeconds,
            'hardwareProfile' => $this->hardwareProfile,
            'consumer' => $this->consumer,
            'preferredEngineId' => $this->preferredEngineId,
            'processingMode' => $this->processingMode,
            'metadata' => $this->metadata,
        ];
    }
}
