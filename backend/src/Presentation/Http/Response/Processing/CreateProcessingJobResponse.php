<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Processing;

use App\Application\Processing\DTO\CreateProcessingJobResult;

final readonly class CreateProcessingJobResponse
{
    public function __construct(
        public string $id,
        public string $status,
        public int $progress,
    ) {
    }

    public static function fromResult(CreateProcessingJobResult $result): self
    {
        return new self(
            $result->processingJobId->value,
            $result->status->value,
            $result->progress,
        );
    }

    /**
     * @return array{id: string, status: string, progress: int}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'progress' => $this->progress,
        ];
    }
}
