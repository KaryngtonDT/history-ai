<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Shadow;

use App\Application\Shadow\DTO\WatchContextResult;
use App\Application\Shadow\WatchContextSegment;

final readonly class WatchContextResponse
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
    ) {
    }

    public static function fromResult(WatchContextResult $result): self
    {
        return new self(
            videoId: $result->videoId,
            currentTimeSeconds: $result->currentTimeSeconds,
            targetLanguage: $result->targetLanguage,
            conversationId: $result->conversationId,
            currentTranscriptSegment: $result->currentTranscriptSegment,
            currentTranslationSegment: $result->currentTranslationSegment,
            previousTranscriptSegment: $result->previousTranscriptSegment,
            nextTranscriptSegment: $result->nextTranscriptSegment,
            previousTranslationSegment: $result->previousTranslationSegment,
            nextTranslationSegment: $result->nextTranslationSegment,
            nearbyTranscriptContext: $result->nearbyTranscriptContext,
            nearbyTranslationContext: $result->nearbyTranslationContext,
            currentSpeaker: $result->currentSpeaker,
            recentInteractions: $result->recentInteractions,
            conversationMemory: $result->conversationMemory,
            engineAnalytics: $result->engineAnalytics,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'videoId' => $this->videoId,
            'currentTimeSeconds' => $this->currentTimeSeconds,
            'targetLanguage' => $this->targetLanguage,
            'conversationId' => $this->conversationId,
            'currentTranscriptSegment' => $this->segmentToArray($this->currentTranscriptSegment),
            'currentTranslationSegment' => $this->segmentToArray($this->currentTranslationSegment),
            'previousTranscriptSegment' => $this->segmentToArray($this->previousTranscriptSegment),
            'nextTranscriptSegment' => $this->segmentToArray($this->nextTranscriptSegment),
            'previousTranslationSegment' => $this->segmentToArray($this->previousTranslationSegment),
            'nextTranslationSegment' => $this->segmentToArray($this->nextTranslationSegment),
            'nearbyTranscriptContext' => $this->nearbyTranscriptContext,
            'nearbyTranslationContext' => $this->nearbyTranslationContext,
            'currentSpeaker' => $this->currentSpeaker,
            'recentInteractions' => $this->recentInteractions,
            'conversationMemory' => $this->conversationMemory,
            'engineAnalytics' => $this->engineAnalytics,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function segmentToArray(?WatchContextSegment $segment): ?array
    {
        return null !== $segment ? $segment->toArray() : null;
    }
}
