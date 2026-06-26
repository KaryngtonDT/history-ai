<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Processing;

use App\Application\Processing\DTO\GetProcessingJobResult;

final readonly class GetProcessingJobResponse
{
    public function __construct(
        public string $id,
        public string $contentId,
        public string $type,
        public string $status,
        public int $progress,
        public ?string $startedAt,
        public ?string $completedAt,
        public ?string $failedAt,
    ) {
    }

    public static function fromResult(GetProcessingJobResult $result): self
    {
        return new self(
            $result->id,
            $result->contentId,
            $result->type,
            $result->status,
            $result->progress,
            $result->startedAt,
            $result->completedAt,
            $result->failedAt,
        );
    }

    /**
     * @return array{
     *     id: string,
     *     contentId: string,
     *     type: string,
     *     status: string,
     *     progress: int,
     *     startedAt: string|null,
     *     completedAt: string|null,
     *     failedAt: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contentId' => $this->contentId,
            'type' => $this->type,
            'status' => $this->status,
            'progress' => $this->progress,
            'startedAt' => $this->startedAt,
            'completedAt' => $this->completedAt,
            'failedAt' => $this->failedAt,
        ];
    }
}
