<?php

declare(strict_types=1);

namespace App\Domain\Speech;

use App\Domain\PipelineJob\TranscriptSource;
use App\Domain\PipelineJob\TranscriptUserChoice;
use DateTimeImmutable;

final readonly class TranscriptMetadata
{
    public function __construct(
        public TranscriptSource $transcriptSource,
        public string $sourceLanguage,
        public ?float $confidence = null,
        public ?DateTimeImmutable $generatedAt = null,
        public bool $selectedByUser = false,
        public ?string $fallbackReason = null,
        public bool $originalCaptionAvailable = false,
        public ?TranscriptUserChoice $userChoice = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'transcriptSource' => $this->transcriptSource->value,
            'sourceLanguage' => $this->sourceLanguage,
            'confidence' => $this->confidence,
            'generatedAt' => $this->generatedAt?->format(DATE_ATOM),
            'selectedByUser' => $this->selectedByUser,
            'fallbackReason' => $this->fallbackReason,
            'originalCaptionAvailable' => $this->originalCaptionAvailable,
            'userChoice' => $this->userChoice?->value,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): ?self
    {
        $source = is_string($data['transcriptSource'] ?? null)
            ? TranscriptSource::tryFrom($data['transcriptSource'])
            : null;

        if (null === $source) {
            return null;
        }

        $userChoice = is_string($data['userChoice'] ?? null)
            ? TranscriptUserChoice::tryFrom($data['userChoice'])
            : null;

        $generatedAt = is_string($data['generatedAt'] ?? null)
            ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['generatedAt']) ?: null
            : null;

        return new self(
            $source,
            is_string($data['sourceLanguage'] ?? null) ? $data['sourceLanguage'] : 'unknown',
            is_numeric($data['confidence'] ?? null) ? (float) $data['confidence'] : null,
            $generatedAt,
            (bool) ($data['selectedByUser'] ?? false),
            is_string($data['fallbackReason'] ?? null) ? $data['fallbackReason'] : null,
            (bool) ($data['originalCaptionAvailable'] ?? false),
            $userChoice,
        );
    }
}
