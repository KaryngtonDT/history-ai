<?php

declare(strict_types=1);

namespace App\Application\Speech\DTO;

use App\Domain\Speech\Transcript;

final readonly class VideoTranscriptResult
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

    public static function fromDomain(string $videoId, Transcript $transcript): self
    {
        /** @var list<array{index: int, startTime: float, endTime: float, text: string}> $segments */
        $segments = [];

        foreach ($transcript->segments()->all() as $segment) {
            $segments[] = [
                'index' => $segment->index(),
                'startTime' => $segment->startTime(),
                'endTime' => $segment->endTime(),
                'text' => $segment->text(),
            ];
        }

        return new self(
            videoId: $videoId,
            transcriptId: $transcript->transcriptId()->value,
            language: $transcript->language()->value,
            text: $transcript->text(),
            duration: $transcript->duration(),
            segmentCount: $transcript->segmentCount(),
            segments: $segments,
        );
    }
}
