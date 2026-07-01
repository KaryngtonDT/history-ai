<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use DateTimeImmutable;

final readonly class ShadowInteraction
{
    public function __construct(
        private ShadowInteractionKind $kind,
        private ShadowParticipant $participant,
        private ShadowTimestamp $videoTimestamp,
        private DateTimeImmutable $recordedAt,
        private ?ShadowQuestion $question = null,
        private ?ShadowAnswer $answer = null,
    ) {
        match ($kind) {
            ShadowInteractionKind::Question => $this->assertQuestion($question, $answer),
            ShadowInteractionKind::Answer => $this->assertAnswer($answer, $question),
            ShadowInteractionKind::Pause, ShadowInteractionKind::Resume => $this->assertCommand(
                $question,
                $answer,
            ),
        };
    }

    public static function createQuestion(
        ShadowQuestion $question,
        ShadowTimestamp $videoTimestamp,
        ?DateTimeImmutable $recordedAt = null,
    ): self {
        return new self(
            ShadowInteractionKind::Question,
            ShadowParticipant::User,
            $videoTimestamp,
            $recordedAt ?? new DateTimeImmutable(),
            $question,
        );
    }

    public static function createAnswer(
        ShadowAnswer $answer,
        ShadowTimestamp $videoTimestamp,
        ?DateTimeImmutable $recordedAt = null,
    ): self {
        return new self(
            ShadowInteractionKind::Answer,
            ShadowParticipant::Shadow,
            $videoTimestamp,
            $recordedAt ?? new DateTimeImmutable(),
            null,
            $answer,
        );
    }

    public static function createPause(
        ShadowTimestamp $videoTimestamp,
        ?DateTimeImmutable $recordedAt = null,
    ): self {
        return new self(
            ShadowInteractionKind::Pause,
            ShadowParticipant::User,
            $videoTimestamp,
            $recordedAt ?? new DateTimeImmutable(),
        );
    }

    public static function createResume(
        ShadowTimestamp $videoTimestamp,
        ?DateTimeImmutable $recordedAt = null,
    ): self {
        return new self(
            ShadowInteractionKind::Resume,
            ShadowParticipant::User,
            $videoTimestamp,
            $recordedAt ?? new DateTimeImmutable(),
        );
    }

    public function kind(): ShadowInteractionKind
    {
        return $this->kind;
    }

    public function participant(): ShadowParticipant
    {
        return $this->participant;
    }

    public function videoTimestamp(): ShadowTimestamp
    {
        return $this->videoTimestamp;
    }

    public function recordedAt(): DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function question(): ?ShadowQuestion
    {
        return $this->question;
    }

    public function answer(): ?ShadowAnswer
    {
        return $this->answer;
    }

    private function assertQuestion(?ShadowQuestion $question, ?ShadowAnswer $answer): void
    {
        if (null === $question || null !== $answer) {
            throw new InvalidShadowSessionException(
                'Question interactions require a question and no answer.',
            );
        }
    }

    private function assertAnswer(?ShadowAnswer $answer, ?ShadowQuestion $question): void
    {
        if (null === $answer || null !== $question) {
            throw new InvalidShadowSessionException(
                'Answer interactions require an answer and no question.',
            );
        }
    }

    private function assertCommand(?ShadowQuestion $question, ?ShadowAnswer $answer): void
    {
        if (null !== $question || null !== $answer) {
            throw new InvalidShadowSessionException(
                'Playback command interactions cannot include question or answer text.',
            );
        }
    }
}
