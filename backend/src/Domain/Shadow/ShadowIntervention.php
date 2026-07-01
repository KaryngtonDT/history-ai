<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;

final readonly class ShadowIntervention
{
    public function __construct(
        private ShadowInterventionId $id,
        private ShadowInterventionType $type,
        private ShadowInterventionTrigger $trigger,
        private string $reason,
        private ShadowTimestamp $videoTimestamp,
        private string $expectedUserAction,
        private bool $allowAutoPause,
        private ?ShadowChallenge $challenge = null,
        private ?string $explanation = null,
        private ?string $suggestedAnswer = null,
        private bool $skipped = false,
        private bool $answered = false,
    ) {
        if ('' === trim($reason)) {
            throw new InvalidShadowSessionException('Shadow intervention must have a reason.');
        }

        if ('' === trim($expectedUserAction)) {
            throw new InvalidShadowSessionException(
                'Shadow intervention must define an expected user action.',
            );
        }

        if ($this->requiresChallenge() && null === $challenge) {
            throw new InvalidShadowSessionException(
                'Challenge interventions must include a challenge question.',
            );
        }
    }

    public static function create(
        ShadowInterventionId $id,
        ShadowInterventionType $type,
        ShadowInterventionTrigger $trigger,
        string $reason,
        ShadowTimestamp $videoTimestamp,
        string $expectedUserAction,
        bool $allowAutoPause,
        ?ShadowChallenge $challenge = null,
        ?string $explanation = null,
        ?string $suggestedAnswer = null,
    ): self {
        return new self(
            $id,
            $type,
            $trigger,
            trim($reason),
            $videoTimestamp,
            trim($expectedUserAction),
            $allowAutoPause,
            $challenge,
            null !== $explanation ? trim($explanation) : null,
            null !== $suggestedAnswer ? trim($suggestedAnswer) : null,
        );
    }

    public function id(): ShadowInterventionId
    {
        return $this->id;
    }

    public function type(): ShadowInterventionType
    {
        return $this->type;
    }

    public function trigger(): ShadowInterventionTrigger
    {
        return $this->trigger;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function videoTimestamp(): ShadowTimestamp
    {
        return $this->videoTimestamp;
    }

    public function expectedUserAction(): string
    {
        return $this->expectedUserAction;
    }

    public function allowAutoPause(): bool
    {
        return $this->allowAutoPause;
    }

    public function challenge(): ?ShadowChallenge
    {
        return $this->challenge;
    }

    public function explanation(): ?string
    {
        return $this->explanation;
    }

    public function suggestedAnswer(): ?string
    {
        return $this->suggestedAnswer;
    }

    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    public function isAnswered(): bool
    {
        return $this->answered;
    }

    public function markSkipped(): self
    {
        return new self(
            $this->id,
            $this->type,
            $this->trigger,
            $this->reason,
            $this->videoTimestamp,
            $this->expectedUserAction,
            $this->allowAutoPause,
            $this->challenge,
            $this->explanation,
            $this->suggestedAnswer,
            skipped: true,
            answered: $this->answered,
        );
    }

    public function markAnswered(): self
    {
        return new self(
            $this->id,
            $this->type,
            $this->trigger,
            $this->reason,
            $this->videoTimestamp,
            $this->expectedUserAction,
            $this->allowAutoPause,
            $this->challenge,
            $this->explanation,
            $this->suggestedAnswer,
            skipped: $this->skipped,
            answered: true,
        );
    }

    private function requiresChallenge(): bool
    {
        return match ($this->type) {
            ShadowInterventionType::ChallengeQuestion,
            ShadowInterventionType::VocabularyCheck,
            ShadowInterventionType::GrammarCheck,
            ShadowInterventionType::ConceptCheck => true,
            default => false,
        };
    }
}
