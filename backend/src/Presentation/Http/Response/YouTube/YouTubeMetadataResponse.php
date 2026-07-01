<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\YouTube;

use App\Domain\YouTube\YouTubeImportResult;
use App\Domain\YouTube\YouTubeMetadata;

final readonly class YouTubeMetadataResponse
{
    public function __construct(
        public string $title,
        public ?int $durationSeconds,
        public ?string $thumbnailUrl,
        public ?string $language,
        public ?string $channelName,
    ) {
    }

    public static function fromMetadata(YouTubeMetadata $metadata): self
    {
        return new self(
            title: $metadata->title,
            durationSeconds: $metadata->durationSeconds,
            thumbnailUrl: $metadata->thumbnailUrl,
            language: $metadata->language,
            channelName: $metadata->channelName,
        );
    }

    /**
     * @return array{
     *     title: string,
     *     durationSeconds: int|null,
     *     thumbnailUrl: string|null,
     *     language: string|null,
     *     channelName: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'durationSeconds' => $this->durationSeconds,
            'thumbnailUrl' => $this->thumbnailUrl,
            'language' => $this->language,
            'channelName' => $this->channelName,
        ];
    }
}
