<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\YouTube;

use App\Application\YouTube\DTO\PreviewYouTubeResult;

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
