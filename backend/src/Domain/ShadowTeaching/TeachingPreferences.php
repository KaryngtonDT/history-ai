<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class TeachingPreferences
{
    public function __construct(
        private bool $teachingEnabled,
        private TeachingVoiceMode $voiceMode,
        private TeachingDifficulty $difficulty,
        private bool $revisionEnabled,
    ) {
    }

    public static function defaults(): self
    {
        return new self(true, TeachingVoiceMode::Professor, TeachingDifficulty::Normal, true);
    }

    public function teachingEnabled(): bool
    {
        return $this->teachingEnabled;
    }

    public function voiceMode(): TeachingVoiceMode
    {
        return $this->voiceMode;
    }

    public function difficulty(): TeachingDifficulty
    {
        return $this->difficulty;
    }

    public function revisionEnabled(): bool
    {
        return $this->revisionEnabled;
    }

    public function withTeachingEnabled(bool $enabled): self
    {
        return new self($enabled, $this->voiceMode, $this->difficulty, $this->revisionEnabled);
    }

    public function withVoiceMode(TeachingVoiceMode $voiceMode): self
    {
        return new self($this->teachingEnabled, $voiceMode, $this->difficulty, $this->revisionEnabled);
    }

    public function withDifficulty(TeachingDifficulty $difficulty): self
    {
        return new self($this->teachingEnabled, $this->voiceMode, $difficulty, $this->revisionEnabled);
    }

    public function withRevisionEnabled(bool $enabled): self
    {
        return new self($this->teachingEnabled, $this->voiceMode, $this->difficulty, $enabled);
    }
}
