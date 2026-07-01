<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Translation\TranslationSegment;
use App\Domain\Translation\TranslationSegmentCollection;

final class CurrentSegmentResolver
{
    public function resolveExact(TranscriptSegmentCollection $segments, float $timeSeconds): ?TranscriptSegment
    {
        foreach ($segments->all() as $segment) {
            if ($timeSeconds >= $segment->startTime() && $timeSeconds <= $segment->endTime()) {
                return $segment;
            }
        }

        return null;
    }

    public function resolveNearest(TranscriptSegmentCollection $segments, float $timeSeconds): ?TranscriptSegment
    {
        $exact = $this->resolveExact($segments, $timeSeconds);

        if (null !== $exact) {
            return $exact;
        }

        $all = $segments->all();

        if ([] === $all) {
            return null;
        }

        $nearest = $all[0];
        $nearestDistance = $this->distanceToSegment($timeSeconds, $nearest);

        foreach ($all as $segment) {
            $distance = $this->distanceToSegment($timeSeconds, $segment);

            if ($distance < $nearestDistance) {
                $nearest = $segment;
                $nearestDistance = $distance;
            }
        }

        return $nearest;
    }

    public function findByIndex(TranscriptSegmentCollection $segments, int $index): ?TranscriptSegment
    {
        foreach ($segments->all() as $segment) {
            if ($segment->index() === $index) {
                return $segment;
            }
        }

        return null;
    }

    public function findTranslationByIndex(
        TranslationSegmentCollection $segments,
        int $index,
    ): ?TranslationSegment {
        foreach ($segments->all() as $segment) {
            if ($segment->index() === $index) {
                return $segment;
            }
        }

        return null;
    }

    private function distanceToSegment(float $timeSeconds, TranscriptSegment $segment): float
    {
        $midpoint = ($segment->startTime() + $segment->endTime()) / 2;

        return abs($timeSeconds - $midpoint);
    }
}
