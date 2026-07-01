<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\TranslationSegmentCollection;

final class TimelineContextBuilder
{
    public const CONTEXT_WINDOW_SIZE = 2;

    public function __construct(
        private readonly CurrentSegmentResolver $segmentResolver,
    ) {
    }

    public function buildNearbyTranscriptContext(
        TranscriptSegmentCollection $segments,
        int $centerIndex,
    ): string {
        $texts = [];

        foreach ($segments->all() as $segment) {
            if ($this->isWithinWindow($segment->index(), $centerIndex)) {
                $texts[] = $segment->text();
            }
        }

        return implode(' ', $texts);
    }

    public function buildNearbyTranslationContext(
        TranslationSegmentCollection $segments,
        int $centerIndex,
    ): string {
        $texts = [];

        foreach ($segments->all() as $segment) {
            if ($this->isWithinWindow($segment->index(), $centerIndex)) {
                $texts[] = $segment->translatedText();
            }
        }

        return implode(' ', $texts);
    }

    public function resolveNeighborTranscriptSegment(
        TranscriptSegmentCollection $segments,
        int $centerIndex,
        int $offset,
    ): ?WatchContextSegment {
        $neighbor = $this->segmentResolver->findByIndex($segments, $centerIndex + $offset);

        if (null === $neighbor) {
            return null;
        }

        return WatchContextSegment::fromTranscript($neighbor);
    }

    public function resolveNeighborTranslationSegment(
        TranscriptSegmentCollection $transcriptSegments,
        TranslationSegmentCollection $translationSegments,
        int $centerIndex,
        int $offset,
    ): ?WatchContextSegment {
        $neighbor = $this->segmentResolver->findByIndex($transcriptSegments, $centerIndex + $offset);

        if (null === $neighbor) {
            return null;
        }

        $translation = $this->segmentResolver->findTranslationByIndex(
            $translationSegments,
            $neighbor->index(),
        );

        return WatchContextSegment::fromTranscript($neighbor, $translation);
    }

    private function isWithinWindow(int $segmentIndex, int $centerIndex): bool
    {
        return $segmentIndex >= ($centerIndex - self::CONTEXT_WINDOW_SIZE)
            && $segmentIndex <= ($centerIndex + self::CONTEXT_WINDOW_SIZE);
    }
}
