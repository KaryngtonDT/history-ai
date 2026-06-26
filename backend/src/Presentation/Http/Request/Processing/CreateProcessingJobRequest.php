<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Processing;

use App\Domain\Processing\ProcessingJobType;
use App\Presentation\Http\Request\Processing\Exception\InvalidProcessingRequestException;

final readonly class CreateProcessingJobRequest
{
    public function __construct(
        public ProcessingJobType $type,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (!isset($payload['type']) || !is_string($payload['type'])) {
            throw new InvalidProcessingRequestException('Type is required.');
        }

        try {
            $type = ProcessingJobType::from($payload['type']);
        } catch (\ValueError) {
            throw new InvalidProcessingRequestException('Type is invalid.');
        }

        return new self($type);
    }
}
