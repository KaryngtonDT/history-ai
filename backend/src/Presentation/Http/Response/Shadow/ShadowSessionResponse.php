<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Shadow;

use App\Application\Shadow\DTO\ShadowAnswerResult;
use App\Application\Shadow\DTO\ShadowSessionResult;

final readonly class ShadowSessionResponse
{
    /**
     * @param list<array<string, mixed>> $interactions
     */
    public function __construct(
        public string $sessionId,
        public string $videoId,
        public string $playbackState,
        public string $targetLanguage,
        public float $currentTimeSeconds,
        public ?int $currentTranscriptSegmentIndex,
        public ?int $currentTranslationSegmentIndex,
        public ?string $contentId,
        public ?string $conversationId,
        public array $interactions,
    ) {
    }

    public static function fromResult(ShadowSessionResult $result): self
    {
        return new self(
            sessionId: $result->sessionId,
            videoId: $result->videoId,
            playbackState: $result->playbackState,
            targetLanguage: $result->targetLanguage,
            currentTimeSeconds: $result->currentTimeSeconds,
            currentTranscriptSegmentIndex: $result->currentTranscriptSegmentIndex,
            currentTranslationSegmentIndex: $result->currentTranslationSegmentIndex,
            contentId: $result->contentId,
            conversationId: $result->conversationId,
            interactions: $result->interactions,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'videoId' => $this->videoId,
            'playbackState' => $this->playbackState,
            'targetLanguage' => $this->targetLanguage,
            'currentTimeSeconds' => $this->currentTimeSeconds,
            'currentTranscriptSegmentIndex' => $this->currentTranscriptSegmentIndex,
            'currentTranslationSegmentIndex' => $this->currentTranslationSegmentIndex,
            'contentId' => $this->contentId,
            'conversationId' => $this->conversationId,
            'interactions' => $this->interactions,
        ];
    }
}
