<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\YouTube;

use App\Application\YouTube\DTO\GetYouTubeResult;
use App\Application\YouTube\DTO\PreviewYouTubeResult;
use App\Domain\YouTube\YouTubeImportResult;

final readonly class ImportYouTubeResponse
{
    public function __construct(
        public string $youtubeId,
        public string $videoId,
        public string $status,
        public string $url,
        public array $metadata,
    ) {
    }

    public static function fromResult(YouTubeImportResult $result): self
    {
        return new self(
            youtubeId: $result->youtubeId->value,
            videoId: $result->videoId->value,
            status: $result->status->value,
            url: $result->url,
            metadata: YouTubeMetadataResponse::fromMetadata($result->metadata)->toArray(),
        );
    }

    /**
     * @return array{
     *     youtubeId: string,
     *     videoId: string,
     *     status: string,
     *     url: string,
     *     metadata: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'youtubeId' => $this->youtubeId,
            'videoId' => $this->videoId,
            'status' => $this->status,
            'url' => $this->url,
            'metadata' => $this->metadata,
        ];
    }
}

final readonly class PreviewYouTubeResponse
{
    public function __construct(
        public string $url,
        public array $metadata,
    ) {
    }

    public static function fromResult(PreviewYouTubeResult $result): self
    {
        return new self(
            url: $result->url,
            metadata: YouTubeMetadataResponse::fromMetadata($result->metadata)->toArray(),
        );
    }

    /**
     * @return array{url: string, metadata: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'metadata' => $this->metadata,
        ];
    }
}

final readonly class GetYouTubeResponse
{
    public function __construct(
        public string $youtubeId,
        public string $videoId,
        public string $url,
        public string $videoStatus,
        public string $importedAt,
        public array $metadata,
    ) {
    }

    public static function fromResult(GetYouTubeResult $result): self
    {
        return new self(
            youtubeId: $result->youtubeId->value,
            videoId: $result->videoId,
            url: $result->url,
            videoStatus: $result->videoStatus->value,
            importedAt: $result->importedAt,
            metadata: YouTubeMetadataResponse::fromMetadata($result->metadata)->toArray(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'youtubeId' => $this->youtubeId,
            'videoId' => $this->videoId,
            'url' => $this->url,
            'videoStatus' => $this->videoStatus,
            'importedAt' => $this->importedAt,
            'metadata' => $this->metadata,
        ];
    }
}
