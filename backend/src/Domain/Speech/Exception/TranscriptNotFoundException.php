<?php

declare(strict_types=1);

namespace App\Domain\Speech\Exception;

final class TranscriptNotFoundException extends InvalidTranscriptException
{
    public function __construct(
        string $message,
        private readonly ?string $videoStatus = null,
        private readonly ?string $failureMessage = null,
        private readonly ?string $failedStage = null,
        private readonly ?float $lastProcessingDurationSeconds = null,
    ) {
        parent::__construct($message);
    }

    public function videoStatus(): ?string
    {
        return $this->videoStatus;
    }

    public function failureMessage(): ?string
    {
        return $this->failureMessage;
    }

    public function failedStage(): ?string
    {
        return $this->failedStage;
    }

    public function lastProcessingDurationSeconds(): ?float
    {
        return $this->lastProcessingDurationSeconds;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return array_filter([
            'error' => 'transcript_not_found',
            'message' => $this->getMessage(),
            'videoStatus' => $this->videoStatus,
            'failureMessage' => $this->failureMessage,
            'failedStage' => $this->failedStage,
            'lastProcessingDurationSeconds' => $this->lastProcessingDurationSeconds,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
