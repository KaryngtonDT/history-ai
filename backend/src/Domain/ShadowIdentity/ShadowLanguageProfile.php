<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\Exception\InvalidShadowIdentityException;

final readonly class ShadowLanguageProfile
{
    public function __construct(
        private string $primaryLanguage,
        private ?string $secondaryLanguage,
        private string $technicalLanguage,
        private ShadowTechnicalLanguagePolicy $technicalTermsPolicy,
        private ShadowPronunciationPolicy $pronunciation,
        private ?string $summaryLanguage,
    ) {
        if ('' === trim($primaryLanguage)) {
            throw new InvalidShadowIdentityException('Primary language cannot be empty.');
        }

        if ('' === trim($technicalLanguage)) {
            throw new InvalidShadowIdentityException('Technical language cannot be empty.');
        }
    }

    public static function default(): self
    {
        return new self(
            primaryLanguage: 'en',
            secondaryLanguage: null,
            technicalLanguage: 'en',
            technicalTermsPolicy: ShadowTechnicalLanguagePolicy::Adaptive,
            pronunciation: ShadowPronunciationPolicy::American,
            summaryLanguage: null,
        );
    }

    public function primaryLanguage(): string
    {
        return $this->primaryLanguage;
    }

    public function secondaryLanguage(): ?string
    {
        return $this->secondaryLanguage;
    }

    public function technicalLanguage(): string
    {
        return $this->technicalLanguage;
    }

    public function technicalTermsPolicy(): ShadowTechnicalLanguagePolicy
    {
        return $this->technicalTermsPolicy;
    }

    public function pronunciation(): ShadowPronunciationPolicy
    {
        return $this->pronunciation;
    }

    public function summaryLanguage(): ?string
    {
        return $this->summaryLanguage;
    }

    public function withPrimaryLanguage(string $language): self
    {
        return new self(
            trim($language),
            $this->secondaryLanguage,
            $this->technicalLanguage,
            $this->technicalTermsPolicy,
            $this->pronunciation,
            $this->summaryLanguage,
        );
    }

    public function withTechnicalTermsPolicy(ShadowTechnicalLanguagePolicy $policy): self
    {
        return new self(
            $this->primaryLanguage,
            $this->secondaryLanguage,
            $this->technicalLanguage,
            $policy,
            $this->pronunciation,
            $this->summaryLanguage,
        );
    }

    public function withPronunciation(ShadowPronunciationPolicy $pronunciation): self
    {
        return new self(
            $this->primaryLanguage,
            $this->secondaryLanguage,
            $this->technicalLanguage,
            $this->technicalTermsPolicy,
            $pronunciation,
            $this->summaryLanguage,
        );
    }
}
