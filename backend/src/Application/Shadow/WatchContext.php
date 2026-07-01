<?php

declare(strict_types=1);

namespace App\Application\Shadow;

final readonly class WatchContext
{
    /**
     * @param list<array{
     *     kind: string,
     *     participant: string,
     *     videoTimestamp: float,
     *     text?: string
     * }> $recentInteractions
     * @param list<string> $conversationMemory
     */
    public function __construct(
        public string $videoId,
        public float $currentTimeSeconds,
        public string $targetLanguage,
        public ?string $conversationId,
        public ?WatchContextSegment $currentTranscriptSegment,
        public ?WatchContextSegment $currentTranslationSegment,
        public ?WatchContextSegment $previousTranscriptSegment,
        public ?WatchContextSegment $nextTranscriptSegment,
        public ?WatchContextSegment $previousTranslationSegment,
        public ?WatchContextSegment $nextTranslationSegment,
        public string $nearbyTranscriptContext,
        public string $nearbyTranslationContext,
        public ?string $currentSpeaker,
        public array $recentInteractions,
        public array $conversationMemory,
    ) {
    }
}
