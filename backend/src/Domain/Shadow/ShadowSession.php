<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

use App\Domain\Chat\ConversationId;
use App\Domain\Content\ContentId;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\VideoId;

final readonly class ShadowSession
{
    public function __construct(
        private ShadowSessionId $id,
        private VideoId $videoId,
        private ?ContentId $contentId,
        private ?ConversationId $conversationId,
        private ShadowTimestamp $currentTimestamp,
        private ShadowPlaybackState $playbackState,
        private string $targetLanguage,
        private ?int $currentTranscriptSegmentIndex,
        private ?int $currentTranslationSegmentIndex,
        private ShadowInteractionCollection $interactions,
    ) {
        if ('' === trim($targetLanguage)) {
            throw new InvalidShadowSessionException('Target language cannot be empty.');
        }

        if (null !== $currentTranscriptSegmentIndex && $currentTranscriptSegmentIndex < 0) {
            throw new InvalidShadowSessionException(
                'Transcript segment index cannot be negative.',
            );
        }

        if (null !== $currentTranslationSegmentIndex && $currentTranslationSegmentIndex < 0) {
            throw new InvalidShadowSessionException(
                'Translation segment index cannot be negative.',
            );
        }
    }

    public static function start(
        ShadowSessionId $id,
        VideoId $videoId,
        string $targetLanguage,
        ?ContentId $contentId = null,
        ?ConversationId $conversationId = null,
    ): self {
        return new self(
            $id,
            $videoId,
            $contentId,
            $conversationId,
            ShadowTimestamp::zero(),
            ShadowPlaybackState::Playing,
            trim($targetLanguage),
            null,
            null,
            ShadowInteractionCollection::empty(),
        );
    }

    public function id(): ShadowSessionId
    {
        return $this->id;
    }

    public function videoId(): VideoId
    {
        return $this->videoId;
    }

    public function contentId(): ?ContentId
    {
        return $this->contentId;
    }

    public function conversationId(): ?ConversationId
    {
        return $this->conversationId;
    }

    public function currentTimestamp(): ShadowTimestamp
    {
        return $this->currentTimestamp;
    }

    public function playbackState(): ShadowPlaybackState
    {
        return $this->playbackState;
    }

    public function targetLanguage(): string
    {
        return $this->targetLanguage;
    }

    public function currentTranscriptSegmentIndex(): ?int
    {
        return $this->currentTranscriptSegmentIndex;
    }

    public function currentTranslationSegmentIndex(): ?int
    {
        return $this->currentTranslationSegmentIndex;
    }

    public function interactions(): ShadowInteractionCollection
    {
        return $this->interactions;
    }

    public function withTimestamp(
        ShadowTimestamp $timestamp,
        ?int $transcriptSegmentIndex = null,
        ?int $translationSegmentIndex = null,
    ): self {
        return new self(
            $this->id,
            $this->videoId,
            $this->contentId,
            $this->conversationId,
            $timestamp,
            $this->playbackState,
            $this->targetLanguage,
            $transcriptSegmentIndex,
            $translationSegmentIndex,
            $this->interactions,
        );
    }

    public function pause(): self
    {
        if (!$this->playbackState->canPause()) {
            throw new InvalidShadowSessionException(
                'Shadow session can only pause from playing state.',
            );
        }

        return new self(
            $this->id,
            $this->videoId,
            $this->contentId,
            $this->conversationId,
            $this->currentTimestamp,
            ShadowPlaybackState::Paused,
            $this->targetLanguage,
            $this->currentTranscriptSegmentIndex,
            $this->currentTranslationSegmentIndex,
            $this->interactions->append(ShadowInteraction::createPause($this->currentTimestamp)),
        );
    }

    public function resume(): self
    {
        if (!$this->playbackState->canResume()) {
            throw new InvalidShadowSessionException(
                'Shadow session can only resume from paused state.',
            );
        }

        return new self(
            $this->id,
            $this->videoId,
            $this->contentId,
            $this->conversationId,
            $this->currentTimestamp,
            ShadowPlaybackState::Playing,
            $this->targetLanguage,
            $this->currentTranscriptSegmentIndex,
            $this->currentTranslationSegmentIndex,
            $this->interactions->append(ShadowInteraction::createResume($this->currentTimestamp)),
        );
    }

    public function end(): self
    {
        if (ShadowPlaybackState::Ended === $this->playbackState) {
            throw new InvalidShadowSessionException('Shadow session is already ended.');
        }

        return new self(
            $this->id,
            $this->videoId,
            $this->contentId,
            $this->conversationId,
            $this->currentTimestamp,
            ShadowPlaybackState::Ended,
            $this->targetLanguage,
            $this->currentTranscriptSegmentIndex,
            $this->currentTranslationSegmentIndex,
            $this->interactions,
        );
    }

    public function recordQuestion(ShadowQuestion $question): self
    {
        return new self(
            $this->id,
            $this->videoId,
            $this->contentId,
            $this->conversationId,
            $this->currentTimestamp,
            $this->playbackState,
            $this->targetLanguage,
            $this->currentTranscriptSegmentIndex,
            $this->currentTranslationSegmentIndex,
            $this->interactions->append(
                ShadowInteraction::createQuestion($question, $this->currentTimestamp),
            ),
        );
    }

    public function recordAnswer(ShadowAnswer $answer): self
    {
        return new self(
            $this->id,
            $this->videoId,
            $this->contentId,
            $this->conversationId,
            $this->currentTimestamp,
            $this->playbackState,
            $this->targetLanguage,
            $this->currentTranscriptSegmentIndex,
            $this->currentTranslationSegmentIndex,
            $this->interactions->append(
                ShadowInteraction::createAnswer($answer, $this->currentTimestamp),
            ),
        );
    }
}
