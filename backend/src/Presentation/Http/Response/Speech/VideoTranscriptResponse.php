<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Speech;

use App\Application\Speech\DTO\VideoTranscriptResult;

final readonly class VideoTranscriptResponse
{
    /**
     * @param list<array{index: int, startTime: float, endTime: float, text: string}> $segments
     */
    public function __construct(
        public string $videoId,
        public string $transcriptId,
        public string $language,
        public string $text,
        public float $duration,
        public int $segmentCount,
        public array $segments,
    ) {
    }

    public static function fromResult(VideoTranscriptResult $result): self
    {
        return new self(
            videoId: $result->videoId,
            transcriptId: $result->transcriptId,
            language: $result->language,
            text: $result->text,
            duration: $result->duration,
            segmentCount: $result->segmentCount,
            segments: $result->segments,
        );
    }

    /**
     * @return array{
     *     videoId: string,
     *     transcriptId: string,
     *     language: string,
     *     text: string,
     *     duration: float,
     *     segmentCount: int,
     *     segments: list<array{index: int, startTime: float, endTime: float, text: string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'videoId' => $this->videoId,
            'transcriptId' => $this->transcriptId,
            'language' => $this->language,
            'text' => $this->text,
            'duration' => $this->duration,
            'segmentCount' => $this->segmentCount,
            'segments' => $this->segments,
        ];
    }
}
