<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\YouTube;

use App\Application\YouTube\DTO\GetYouTubeResult;

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
