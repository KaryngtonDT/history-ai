<?php

declare(strict_types=1);

namespace App\Domain\Speech;

final readonly class Transcript
{
    public function __construct(
        private TranscriptId $transcriptId,
        private TranscriptLanguage $language,
        private TranscriptSegmentCollection $segments,
    ) {
    }

    public static function create(
        TranscriptId $transcriptId,
        TranscriptLanguage $language,
        TranscriptSegmentCollection $segments,
    ): self {
        return new self($transcriptId, $language, $segments);
    }

    public function transcriptId(): TranscriptId
    {
        return $this->transcriptId;
    }

    public function language(): TranscriptLanguage
    {
        return $this->language;
    }

    public function segments(): TranscriptSegmentCollection
    {
        return $this->segments;
    }

    public function text(): string
    {
        if ($this->segments->isEmpty()) {
            return '';
        }

        return implode(
            ' ',
            array_map(
                static fn (TranscriptSegment $segment): string => $segment->text(),
                $this->segments->all(),
            ),
        );
    }

    public function duration(): float
    {
        $duration = 0.0;

        foreach ($this->segments->all() as $segment) {
            $duration = max($duration, $segment->endTime());
        }

        return $duration;
    }

    public function segmentCount(): int
    {
        return $this->segments->count();
    }
}
