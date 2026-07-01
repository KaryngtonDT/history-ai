<?php

declare(strict_types=1);

namespace App\Application\Shadow\DTO;

use App\Domain\Shadow\ShadowSession;

final readonly class ShadowSessionResult
{
    /**
     * @param list<array{
     *     kind: string,
     *     participant: string,
     *     videoTimestamp: float,
     *     text?: string
     * }> $interactions
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
        public ShadowInterventionPolicyResult $policy,
    ) {
    }

    public static function fromDomain(ShadowSession $session): self
    {
        $interactions = [];

        foreach ($session->interactions()->all() as $interaction) {
            $entry = [
                'kind' => $interaction->kind()->value,
                'participant' => $interaction->participant()->value,
                'videoTimestamp' => $interaction->videoTimestamp()->seconds(),
            ];

            if (null !== $interaction->question()) {
                $entry['text'] = $interaction->question()->text();
            }

            if (null !== $interaction->answer()) {
                $entry['text'] = $interaction->answer()->text();
            }

            $interactions[] = $entry;
        }

        return new self(
            sessionId: $session->id()->value,
            videoId: $session->videoId()->value,
            playbackState: $session->playbackState()->value,
            targetLanguage: $session->targetLanguage(),
            currentTimeSeconds: $session->currentTimestamp()->seconds(),
            currentTranscriptSegmentIndex: $session->currentTranscriptSegmentIndex(),
            currentTranslationSegmentIndex: $session->currentTranslationSegmentIndex(),
            contentId: $session->contentId()?->value,
            conversationId: $session->conversationId()?->value,
            interactions: $interactions,
            policy: ShadowInterventionPolicyResult::fromDomain($session->interventionPolicy()),
        );
    }
}
