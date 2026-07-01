<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Application\Translation\TranslationLanguageListParser;
use App\Domain\Chat\ConversationId;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Shadow\ShadowConversationContextInterface;
use App\Domain\Shadow\ShadowInteraction;
use App\Domain\Shadow\ShadowInteractionKind;
use App\Domain\Shadow\ShadowSession;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Translation\TranslationSegmentCollection;
use App\Domain\Video\VideoId;

final class ShadowContextFactory
{
    public function __construct(
        private readonly TranscriptRepositoryInterface $transcriptRepository,
        private readonly TranslationRepositoryInterface $translationRepository,
        private readonly CurrentSegmentResolver $segmentResolver,
        private readonly TimelineContextBuilder $timelineContextBuilder,
        private readonly ShadowSessionRepositoryInterface $sessionRepository,
        private readonly ShadowConversationContextInterface $conversationContext,
    ) {
    }

    public function create(
        string $videoId,
        float $currentTimeSeconds,
        string $targetLanguage,
        ?string $conversationId = null,
    ): WatchContext {
        if ($currentTimeSeconds < 0) {
            throw new InvalidShadowSessionException('Shadow context time cannot be negative.');
        }

        $id = new VideoId($videoId);
        $transcript = $this->transcriptRepository->findByVideoId($id);

        if (null === $transcript) {
            throw new InvalidShadowSessionException(sprintf(
                'Transcript for video "%s" was not found.',
                $videoId,
            ));
        }

        $parsedLanguages = TranslationLanguageListParser::parse($targetLanguage);
        $translation = [] !== $parsedLanguages
            ? $this->translationRepository->findByVideoIdAndLanguage($id, $parsedLanguages[0])
            : null;
        $translationSegments = $translation?->segments() ?? TranslationSegmentCollection::empty();

        $currentTranscript = $this->segmentResolver->resolveNearest($transcript->segments(), $currentTimeSeconds);
        $centerIndex = $currentTranscript?->index() ?? 0;

        $currentTranslationSegment = null !== $currentTranscript
            ? $this->segmentResolver->findTranslationByIndex($translationSegments, $currentTranscript->index())
            : null;

        $currentTranscriptSegment = null !== $currentTranscript
            ? WatchContextSegment::fromTranscript($currentTranscript, $currentTranslationSegment)
            : null;

        $currentTranslationContextSegment = null !== $currentTranscript && null !== $currentTranslationSegment
            ? WatchContextSegment::fromTranscript($currentTranscript, $currentTranslationSegment)
            : null;

        $nearbyTranslationContext = $translationSegments->isEmpty()
            ? ''
            : $this->timelineContextBuilder->buildNearbyTranslationContext($translationSegments, $centerIndex);

        return new WatchContext(
            videoId: $videoId,
            currentTimeSeconds: $currentTimeSeconds,
            targetLanguage: $targetLanguage,
            conversationId: $conversationId,
            currentTranscriptSegment: $currentTranscriptSegment,
            currentTranslationSegment: $currentTranslationContextSegment,
            previousTranscriptSegment: $this->timelineContextBuilder->resolveNeighborTranscriptSegment(
                $transcript->segments(),
                $centerIndex,
                -1,
            ),
            nextTranscriptSegment: $this->timelineContextBuilder->resolveNeighborTranscriptSegment(
                $transcript->segments(),
                $centerIndex,
                1,
            ),
            previousTranslationSegment: $this->timelineContextBuilder->resolveNeighborTranslationSegment(
                $transcript->segments(),
                $translationSegments,
                $centerIndex,
                -1,
            ),
            nextTranslationSegment: $this->timelineContextBuilder->resolveNeighborTranslationSegment(
                $transcript->segments(),
                $translationSegments,
                $centerIndex,
                1,
            ),
            nearbyTranscriptContext: $this->timelineContextBuilder->buildNearbyTranscriptContext(
                $transcript->segments(),
                $centerIndex,
            ),
            nearbyTranslationContext: $nearbyTranslationContext,
            currentSpeaker: $this->resolveSpeaker($currentTranscript),
            recentInteractions: $this->resolveRecentInteractions($id),
            conversationMemory: $this->resolveConversationMemory($conversationId),
        );
    }

    private function resolveSpeaker(?TranscriptSegment $segment): ?string
    {
        return null;
    }

    /**
     * @return list<array{
     *     kind: string,
     *     participant: string,
     *     videoTimestamp: float,
     *     text?: string
     * }>
     */
    private function resolveRecentInteractions(VideoId $videoId): array
    {
        $sessions = $this->sessionRepository->findByVideoId($videoId);

        if ([] === $sessions) {
            return [];
        }

        $session = $this->findMostRecentSession($sessions);

        return array_map(
            static fn (ShadowInteraction $interaction): array => self::interactionToArray($interaction),
            $session->interactions()->recent(6),
        );
    }

    /**
     * @param list<ShadowSession> $sessions
     */
    private function findMostRecentSession(array $sessions): ShadowSession
    {
        usort(
            $sessions,
            static fn (ShadowSession $left, ShadowSession $right): int => $right->interactions()->count()
                <=> $left->interactions()->count(),
        );

        return $sessions[0];
    }

    /**
     * @return list<string>
     */
    private function resolveConversationMemory(?string $conversationId): array
    {
        if (null === $conversationId || '' === trim($conversationId)) {
            return [];
        }

        return $this->conversationContext->loadRecentMessages(new ConversationId($conversationId));
    }

    /**
     * @return array{
     *     kind: string,
     *     participant: string,
     *     videoTimestamp: float,
     *     text?: string
     * }
     */
    private static function interactionToArray(ShadowInteraction $interaction): array
    {
        $data = [
            'kind' => $interaction->kind()->value,
            'participant' => $interaction->participant()->value,
            'videoTimestamp' => $interaction->videoTimestamp()->seconds(),
        ];

        if (ShadowInteractionKind::Question === $interaction->kind() && null !== $interaction->question()) {
            $data['text'] = $interaction->question()->text();
        }

        if (ShadowInteractionKind::Answer === $interaction->kind() && null !== $interaction->answer()) {
            $data['text'] = $interaction->answer()->text();
        }

        return $data;
    }
}
