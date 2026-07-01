<?php

declare(strict_types=1);

namespace App\Application\Shadow\DTO;

use App\Domain\Shadow\ShadowChallenge;
use App\Domain\Shadow\ShadowIntervention;

final readonly class ShadowInterventionResult
{
    public function __construct(
        public string $id,
        public string $type,
        public string $trigger,
        public string $reason,
        public float $videoTimestamp,
        public string $expectedUserAction,
        public bool $allowAutoPause,
        public ?string $explanation,
        public ?ShadowChallengeResult $challenge,
        public bool $skipped,
        public bool $answered,
    ) {
    }

    public static function fromDomain(ShadowIntervention $intervention): self
    {
        return new self(
            id: $intervention->id()->value,
            type: $intervention->type()->value,
            trigger: $intervention->trigger()->value,
            reason: $intervention->reason(),
            videoTimestamp: $intervention->videoTimestamp()->seconds(),
            expectedUserAction: $intervention->expectedUserAction(),
            allowAutoPause: $intervention->allowAutoPause(),
            explanation: $intervention->explanation(),
            challenge: null !== $intervention->challenge()
                ? ShadowChallengeResult::fromDomain($intervention->challenge())
                : null,
            skipped: $intervention->isSkipped(),
            answered: $intervention->isAnswered(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'type' => $this->type,
            'trigger' => $this->trigger,
            'reason' => $this->reason,
            'videoTimestamp' => $this->videoTimestamp,
            'expectedUserAction' => $this->expectedUserAction,
            'allowAutoPause' => $this->allowAutoPause,
            'skipped' => $this->skipped,
            'answered' => $this->answered,
        ];

        if (null !== $this->explanation) {
            $data['explanation'] = $this->explanation;
        }

        if (null !== $this->challenge) {
            $data['challenge'] = $this->challenge->toArray();
        }

        return $data;
    }
}
