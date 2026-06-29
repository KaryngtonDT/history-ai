<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Video;

use App\Application\Video\DTO\UploadVideoResult;

final readonly class UploadVideoResponse
{
    public function __construct(
        public string $videoId,
        public string $status,
    ) {
    }

    public static function fromResult(UploadVideoResult $result): self
    {
        return new self(
            videoId: $result->videoId->value,
            status: $result->status->value,
        );
    }

    /**
     * @return array{videoId: string, status: string}
     */
    public function toArray(): array
    {
        return [
            'videoId' => $this->videoId,
            'status' => $this->status,
        ];
    }
}
