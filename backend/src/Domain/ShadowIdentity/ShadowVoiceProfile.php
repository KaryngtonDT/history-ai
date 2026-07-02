<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\Exception\InvalidShadowIdentityException;

final readonly class ShadowVoiceProfile
{
    public function __construct(
        private string $voiceId,
        private string $engine,
        private float $speed,
        private float $pitch,
        private int $warmth,
        private int $energy,
        private int $emotion,
        private int $pauses,
        private int $expressiveness,
        private bool $thinkingPauses,
        private ShadowHumorLevel $humor,
    ) {
        if ('' === trim($voiceId)) {
            throw new InvalidShadowIdentityException('Voice id cannot be empty.');
        }

        if ('' === trim($engine)) {
            throw new InvalidShadowIdentityException('Voice engine cannot be empty.');
        }

        if ($speed < 0.5 || $speed > 2.0) {
            throw new InvalidShadowIdentityException('Voice speed must be between 0.5 and 2.0.');
        }

        if ($pitch < 0.5 || $pitch > 2.0) {
            throw new InvalidShadowIdentityException('Voice pitch must be between 0.5 and 2.0.');
        }

        foreach ([$warmth, $energy, $emotion, $pauses, $expressiveness] as $value) {
            if ($value < 0 || $value > 10) {
                throw new InvalidShadowIdentityException('Voice profile scale values must be between 0 and 10.');
            }
        }
    }

    public static function default(): self
    {
        return new self(
            voiceId: 'browser-default',
            engine: 'browser_tts',
            speed: 1.0,
            pitch: 1.0,
            warmth: 6,
            energy: 6,
            emotion: 5,
            pauses: 5,
            expressiveness: 6,
            thinkingPauses: true,
            humor: ShadowHumorLevel::Low,
        );
    }

    public function voiceId(): string
    {
        return $this->voiceId;
    }

    public function engine(): string
    {
        return $this->engine;
    }

    public function speed(): float
    {
        return $this->speed;
    }

    public function pitch(): float
    {
        return $this->pitch;
    }

    public function warmth(): int
    {
        return $this->warmth;
    }

    public function energy(): int
    {
        return $this->energy;
    }

    public function emotion(): int
    {
        return $this->emotion;
    }

    public function pauses(): int
    {
        return $this->pauses;
    }

    public function expressiveness(): int
    {
        return $this->expressiveness;
    }

    public function thinkingPausesEnabled(): bool
    {
        return $this->thinkingPauses;
    }

    public function humor(): ShadowHumorLevel
    {
        return $this->humor;
    }

    public function withSpeed(float $speed): self
    {
        return new self(
            $this->voiceId,
            $this->engine,
            $speed,
            $this->pitch,
            $this->warmth,
            $this->energy,
            $this->emotion,
            $this->pauses,
            $this->expressiveness,
            $this->thinkingPauses,
            $this->humor,
        );
    }

    public function withVoice(string $voiceId, string $engine): self
    {
        return new self(
            $voiceId,
            $engine,
            $this->speed,
            $this->pitch,
            $this->warmth,
            $this->energy,
            $this->emotion,
            $this->pauses,
            $this->expressiveness,
            $this->thinkingPauses,
            $this->humor,
        );
    }

    public function withHumor(ShadowHumorLevel $humor): self
    {
        return new self(
            $this->voiceId,
            $this->engine,
            $this->speed,
            $this->pitch,
            $this->warmth,
            $this->energy,
            $this->emotion,
            $this->pauses,
            $this->expressiveness,
            $this->thinkingPauses,
            $humor,
        );
    }
}
