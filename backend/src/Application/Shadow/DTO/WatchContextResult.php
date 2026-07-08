<?php

declare(strict_types=1);

namespace App\Application\Shadow\DTO;

use App\Application\Shadow\WatchContext;
use App\Application\Shadow\WatchContextSegment;

final readonly class WatchContextResult
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
        public ?array $engineAnalytics = null,
        public ?array $runtimeContext = null,
    ) {
    }

    public static function fromWatchContext(
        WatchContext $context,
        ?array $engineAnalytics = null,
        ?array $runtimeContext = null,
    ): self {
        return new self(
            videoId: $context->videoId,
            currentTimeSeconds: $context->currentTimeSeconds,
            targetLanguage: $context->targetLanguage,
            conversationId: $context->conversationId,
            currentTranscriptSegment: $context->currentTranscriptSegment,
            currentTranslationSegment: $context->currentTranslationSegment,
            previousTranscriptSegment: $context->previousTranscriptSegment,
            nextTranscriptSegment: $context->nextTranscriptSegment,
            previousTranslationSegment: $context->previousTranslationSegment,
            nextTranslationSegment: $context->nextTranslationSegment,
            nearbyTranscriptContext: $context->nearbyTranscriptContext,
            nearbyTranslationContext: $context->nearbyTranslationContext,
            currentSpeaker: $context->currentSpeaker,
            recentInteractions: $context->recentInteractions,
            conversationMemory: $context->conversationMemory,
            engineAnalytics: $engineAnalytics,
            runtimeContext: $runtimeContext,
        );
    }
}
