<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;

final readonly class ShadowVoicePreference
{
    public function __construct(
        private ShadowVoiceMode $mode,
        private ?ShadowVoiceLanguage $manualLanguage = null,
    ) {
        if (ShadowVoiceMode::Manual === $this->mode && null === $this->manualLanguage) {
            throw new InvalidShadowSessionException(
                'Manual Shadow voice mode requires an explicit language.',
            );
        }
    }

    public static function default(): self
    {
        return new self(ShadowVoiceMode::SameAsTargetLanguage);
    }

    public function mode(): ShadowVoiceMode
    {
        return $this->mode;
    }

    public function manualLanguage(): ?ShadowVoiceLanguage
    {
        return $this->manualLanguage;
    }

    public function withMode(ShadowVoiceMode $mode, ?ShadowVoiceLanguage $manualLanguage = null): self
    {
        if (ShadowVoiceMode::Manual === $mode) {
            return new self($mode, $manualLanguage ?? $this->manualLanguage);
        }

        return new self($mode);
    }

    public function withManualLanguage(ShadowVoiceLanguage $language): self
    {
        return new self(ShadowVoiceMode::Manual, $language);
    }

    public function resolve(
        string $targetLanguage,
        ?ShadowVoiceLanguage $interfaceLanguage = null,
    ): ShadowVoiceLanguage {
        return match ($this->mode) {
            ShadowVoiceMode::SameAsTargetLanguage => ShadowVoiceLanguage::tryFromTargetLanguage($targetLanguage)
                ?? ShadowVoiceLanguage::fallback(),
            ShadowVoiceMode::SameAsInterface => $interfaceLanguage ?? ShadowVoiceLanguage::fallback(),
            ShadowVoiceMode::Manual => $this->manualLanguage ?? ShadowVoiceLanguage::fallback(),
        };
    }
}
