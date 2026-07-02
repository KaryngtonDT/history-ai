<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\Exception\InvalidShadowIdentityException;

final readonly class ShadowPersonaTraits
{
    public function __construct(
        public int $tone,
        public int $energy,
        public int $warmth,
        public int $verbosity,
        public int $examples,
        public int $analogies,
        public int $challenge,
        public int $humor,
        public int $emotion,
        public int $storytelling,
        public ShadowThinkingStyle $thinkingStyle,
        public int $interactionRhythm,
    ) {
        foreach (
            [
                $tone,
                $energy,
                $warmth,
                $verbosity,
                $examples,
                $analogies,
                $challenge,
                $humor,
                $emotion,
                $storytelling,
                $interactionRhythm,
            ] as $value
        ) {
            if ($value < 0 || $value > 10) {
                throw new InvalidShadowIdentityException('Persona trait values must be between 0 and 10.');
            }
        }
    }

    public function withChallenge(int $challenge): self
    {
        return new self(
            $this->tone,
            $this->energy,
            $this->warmth,
            $this->verbosity,
            $this->examples,
            $this->analogies,
            $challenge,
            $this->humor,
            $this->emotion,
            $this->storytelling,
            $this->thinkingStyle,
            $this->interactionRhythm,
        );
    }

    public function withExamples(int $examples): self
    {
        return new self(
            $this->tone,
            $this->energy,
            $this->warmth,
            $this->verbosity,
            $examples,
            $this->analogies,
            $this->challenge,
            $this->humor,
            $this->emotion,
            $this->storytelling,
            $this->thinkingStyle,
            $this->interactionRhythm,
        );
    }

    public function withHumor(int $humor): self
    {
        return new self(
            $this->tone,
            $this->energy,
            $this->warmth,
            $this->verbosity,
            $this->examples,
            $this->analogies,
            $this->challenge,
            $humor,
            $this->emotion,
            $this->storytelling,
            $this->thinkingStyle,
            $this->interactionRhythm,
        );
    }
}
