<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Application\Shadow\DTO\ShadowAnswerVoiceMetadata;
use App\Domain\Shadow\ShadowVoiceLanguage;
use App\Domain\Shadow\ShadowVoiceMode;
use App\Domain\Shadow\ShadowVoicePreference;

final class ShadowAnswerLanguageResolver
{
    public function resolve(
        string $questionText,
        string $targetLanguage,
        ShadowVoicePreference $preference,
        ?ShadowVoiceLanguage $interfaceLanguage = null,
    ): ShadowAnswerVoiceMetadata {
        $explicit = $this->detectExplicitLanguage($questionText);

        if (null !== $explicit) {
            return new ShadowAnswerVoiceMetadata(
                answerLanguage: $explicit,
                speechLanguage: $explicit,
                fallbackUsed: false,
                reason: 'explicit_user_override',
            );
        }

        $resolved = $preference->resolve($targetLanguage, $interfaceLanguage);
        $fallbackUsed = $this->usedFallback($preference, $targetLanguage, $interfaceLanguage, $resolved);
        $reason = match ($preference->mode()) {
            ShadowVoiceMode::SameAsTargetLanguage => $fallbackUsed
                ? 'target_language_fallback'
                : 'target_language',
            ShadowVoiceMode::SameAsInterface => null === $interfaceLanguage
                ? 'interface_language_fallback'
                : 'interface_language',
            ShadowVoiceMode::Manual => 'manual_selection',
        };

        return new ShadowAnswerVoiceMetadata(
            answerLanguage: $resolved,
            speechLanguage: $resolved,
            fallbackUsed: $fallbackUsed,
            reason: $reason,
        );
    }

    private function detectExplicitLanguage(string $questionText): ?ShadowVoiceLanguage
    {
        $question = mb_strtolower(trim($questionText));

        if ($this->matchesAny($question, [
            'explique en français',
            'explain in french',
            'réponds en français',
            'answer in french',
            'en français',
        ])) {
            return ShadowVoiceLanguage::French;
        }

        if ($this->matchesAny($question, [
            'answer in english',
            'explain in english',
            'réponds en anglais',
            'explique en anglais',
            'in english',
        ])) {
            return ShadowVoiceLanguage::English;
        }

        if ($this->matchesAny($question, [
            'auf deutsch erklären',
            'auf deutsch erklaeren',
            'answer in german',
            'explain in german',
            'réponds en allemand',
            'explique en allemand',
            'auf deutsch',
        ])) {
            return ShadowVoiceLanguage::German;
        }

        return null;
    }

    /**
     * @param list<string> $patterns
     */
    private function matchesAny(string $question, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_contains($question, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function usedFallback(
        ShadowVoicePreference $preference,
        string $targetLanguage,
        ?ShadowVoiceLanguage $interfaceLanguage,
        ShadowVoiceLanguage $resolved,
    ): bool {
        if (ShadowVoiceLanguage::English !== $resolved) {
            return false;
        }

        return match ($preference->mode()) {
            ShadowVoiceMode::SameAsTargetLanguage => null === ShadowVoiceLanguage::tryFromTargetLanguage($targetLanguage),
            ShadowVoiceMode::SameAsInterface => null === $interfaceLanguage,
            ShadowVoiceMode::Manual => null === $preference->manualLanguage(),
        };
    }
}
