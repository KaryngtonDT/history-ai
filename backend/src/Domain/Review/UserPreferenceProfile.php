<?php

declare(strict_types=1);

namespace App\Domain\Review;

final readonly class UserPreferenceProfile
{
    public function __construct(
        private TranslationStylePreference $translationStyle,
        private VoiceStabilityPreference $voiceStability,
        private RenderingPresetPreference $renderingPreset,
        private LipSyncStrengthPreference $lipSyncStrength,
    ) {
    }

    public static function create(
        TranslationStylePreference $translationStyle,
        VoiceStabilityPreference $voiceStability,
        RenderingPresetPreference $renderingPreset,
        LipSyncStrengthPreference $lipSyncStrength,
    ): self {
        return new self(
            $translationStyle,
            $voiceStability,
            $renderingPreset,
            $lipSyncStrength,
        );
    }

    public static function deriveFromReviews(ReviewCollection $reviews): self
    {
        $averages = $reviews->averageScores();

        return new self(
            self::resolveTranslationStyle($averages[ReviewCategory::Translation->value]),
            self::resolveVoiceStability($averages[ReviewCategory::VoiceClone->value]),
            self::resolveRenderingPreset($averages[ReviewCategory::Rendering->value]),
            self::resolveLipSyncStrength($averages[ReviewCategory::LipSync->value]),
        );
    }

    public function translationStyle(): TranslationStylePreference
    {
        return $this->translationStyle;
    }

    public function voiceStability(): VoiceStabilityPreference
    {
        return $this->voiceStability;
    }

    public function renderingPreset(): RenderingPresetPreference
    {
        return $this->renderingPreset;
    }

    public function lipSyncStrength(): LipSyncStrengthPreference
    {
        return $this->lipSyncStrength;
    }

    /**
     * @return list<string>
     */
    public function explanationLines(): array
    {
        $lines = [];

        $lines[] = match ($this->voiceStability) {
            VoiceStabilityPreference::High => 'Using your preferred voice profile with increased stability.',
            VoiceStabilityPreference::Medium => 'Using a balanced voice profile based on your feedback.',
            VoiceStabilityPreference::Low => 'Using a more expressive voice profile based on your feedback.',
        };

        $lines[] = match ($this->lipSyncStrength) {
            LipSyncStrengthPreference::Strong => 'Lip sync strength increased according to previous feedback.',
            LipSyncStrengthPreference::Moderate => 'Lip sync strength balanced according to previous feedback.',
            LipSyncStrengthPreference::Subtle => 'Lip sync strength reduced according to previous feedback.',
        };

        $lines[] = match ($this->translationStyle) {
            TranslationStylePreference::Natural => 'Natural translation style preferred.',
            TranslationStylePreference::Literal => 'Literal translation style preferred.',
            TranslationStylePreference::Balanced => 'Balanced translation style preferred.',
        };

        $lines[] = match ($this->renderingPreset) {
            RenderingPresetPreference::Quality => 'Quality rendering preset preferred.',
            RenderingPresetPreference::Balanced => 'Balanced rendering preset preferred.',
            RenderingPresetPreference::Speed => 'Speed rendering preset preferred.',
        };

        return $lines;
    }

    private static function resolveTranslationStyle(ReviewScore $score): TranslationStylePreference
    {
        return match (true) {
            $score->value() >= 4 => TranslationStylePreference::Natural,
            $score->value() <= 2 => TranslationStylePreference::Literal,
            default => TranslationStylePreference::Balanced,
        };
    }

    private static function resolveVoiceStability(ReviewScore $score): VoiceStabilityPreference
    {
        return match (true) {
            $score->value() <= 2 => VoiceStabilityPreference::High,
            $score->value() >= 4 => VoiceStabilityPreference::Low,
            default => VoiceStabilityPreference::Medium,
        };
    }

    private static function resolveRenderingPreset(ReviewScore $score): RenderingPresetPreference
    {
        return match (true) {
            $score->value() >= 4 => RenderingPresetPreference::Quality,
            $score->value() <= 2 => RenderingPresetPreference::Speed,
            default => RenderingPresetPreference::Balanced,
        };
    }

    private static function resolveLipSyncStrength(ReviewScore $score): LipSyncStrengthPreference
    {
        return match (true) {
            $score->value() <= 2 => LipSyncStrengthPreference::Subtle,
            $score->value() >= 4 => LipSyncStrengthPreference::Strong,
            default => LipSyncStrengthPreference::Moderate,
        };
    }
}
