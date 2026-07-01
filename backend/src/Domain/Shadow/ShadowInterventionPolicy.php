<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;

final readonly class ShadowInterventionPolicy
{
    public function __construct(
        private bool $enabled,
        private int $maxInterventionsPerMinute,
        private float $minSecondsBetweenInterventions,
        private ShadowChallengeLevel $challengeLevel,
        private ShadowExplanationStyle $explanationStyle,
        private bool $autoResume,
        private bool $allowAutoPause,
    ) {
        if ($maxInterventionsPerMinute < 1) {
            throw new InvalidShadowSessionException(
                'Max interventions per minute must be at least 1.',
            );
        }

        if ($minSecondsBetweenInterventions < 0) {
            throw new InvalidShadowSessionException(
                'Minimum seconds between interventions cannot be negative.',
            );
        }
    }

    public static function disabled(): self
    {
        return new self(
            enabled: false,
            maxInterventionsPerMinute: 1,
            minSecondsBetweenInterventions: 60.0,
            challengeLevel: ShadowChallengeLevel::Easy,
            explanationStyle: ShadowExplanationStyle::Short,
            autoResume: false,
            allowAutoPause: false,
        );
    }

    public static function gentleDefault(): self
    {
        return new self(
            enabled: true,
            maxInterventionsPerMinute: 2,
            minSecondsBetweenInterventions: 45.0,
            challengeLevel: ShadowChallengeLevel::Easy,
            explanationStyle: ShadowExplanationStyle::Short,
            autoResume: false,
            allowAutoPause: true,
        );
    }

    public static function normalDefault(): self
    {
        return new self(
            enabled: true,
            maxInterventionsPerMinute: 4,
            minSecondsBetweenInterventions: 30.0,
            challengeLevel: ShadowChallengeLevel::Normal,
            explanationStyle: ShadowExplanationStyle::Detailed,
            autoResume: true,
            allowAutoPause: true,
        );
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function maxInterventionsPerMinute(): int
    {
        return $this->maxInterventionsPerMinute;
    }

    public function minSecondsBetweenInterventions(): float
    {
        return $this->minSecondsBetweenInterventions;
    }

    public function challengeLevel(): ShadowChallengeLevel
    {
        return $this->challengeLevel;
    }

    public function explanationStyle(): ShadowExplanationStyle
    {
        return $this->explanationStyle;
    }

    public function autoResume(): bool
    {
        return $this->autoResume;
    }

    public function allowAutoPause(): bool
    {
        return $this->allowAutoPause;
    }

    public function withEnabled(bool $enabled): self
    {
        return new self(
            $enabled,
            $this->maxInterventionsPerMinute,
            $this->minSecondsBetweenInterventions,
            $this->challengeLevel,
            $this->explanationStyle,
            $this->autoResume,
            $this->allowAutoPause,
        );
    }

    public function withChallengeLevel(ShadowChallengeLevel $level): self
    {
        return new self(
            $this->enabled,
            $this->maxInterventionsPerMinute,
            $this->minSecondsBetweenInterventions,
            $level,
            $this->explanationStyle,
            $this->autoResume,
            $this->allowAutoPause,
        );
    }

    public function withExplanationStyle(ShadowExplanationStyle $style): self
    {
        return new self(
            $this->enabled,
            $this->maxInterventionsPerMinute,
            $this->minSecondsBetweenInterventions,
            $this->challengeLevel,
            $style,
            $this->autoResume,
            $this->allowAutoPause,
        );
    }

    public function withAutoResume(bool $autoResume): self
    {
        return new self(
            $this->enabled,
            $this->maxInterventionsPerMinute,
            $this->minSecondsBetweenInterventions,
            $this->challengeLevel,
            $this->explanationStyle,
            $autoResume,
            $this->allowAutoPause,
        );
    }

    public function withAllowAutoPause(bool $allowAutoPause): self
    {
        return new self(
            $this->enabled,
            $this->maxInterventionsPerMinute,
            $this->minSecondsBetweenInterventions,
            $this->challengeLevel,
            $this->explanationStyle,
            $this->autoResume,
            $allowAutoPause,
        );
    }

    public function withFrequency(int $maxPerMinute, float $minSecondsBetween): self
    {
        return new self(
            $this->enabled,
            $maxPerMinute,
            $minSecondsBetween,
            $this->challengeLevel,
            $this->explanationStyle,
            $this->autoResume,
            $this->allowAutoPause,
        );
    }

    public function canScheduleIntervention(
        float $currentTimeSeconds,
        ShadowInterventionCollection $recentInterventions,
    ): bool {
        if (!$this->enabled) {
            return false;
        }

        $interventions = $recentInterventions->all();

        if ([] === $interventions) {
            return true;
        }

        $last = $interventions[array_key_last($interventions)];
        $secondsSinceLast = $currentTimeSeconds - $last->videoTimestamp()->seconds();

        if ($secondsSinceLast < $this->minSecondsBetweenInterventions) {
            return false;
        }

        $windowStart = $currentTimeSeconds - 60.0;
        $countInWindow = 0;

        foreach ($interventions as $intervention) {
            if ($intervention->videoTimestamp()->seconds() >= $windowStart) {
                ++$countInWindow;
            }
        }

        return $countInWindow < $this->maxInterventionsPerMinute;
    }
}
