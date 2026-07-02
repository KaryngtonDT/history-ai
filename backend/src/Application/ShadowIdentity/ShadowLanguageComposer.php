<?php

declare(strict_types=1);

namespace App\Application\ShadowIdentity;

use App\Domain\ShadowIdentity\ShadowIdentityPreferences;
use App\Domain\ShadowIdentity\ShadowLanguageProfile;
use App\Domain\ShadowIdentity\ShadowPronunciationPolicy;
use App\Domain\ShadowIdentity\ShadowTechnicalLanguagePolicy;

final class ShadowLanguageComposer
{
    /**
     * @return list<string>
     */
    public function composeInstructions(ShadowIdentityPreferences $preferences): array
    {
        $profile = $preferences->languageProfile();
        $lines = [
            sprintf('Primary explanation language: %s.', $profile->primaryLanguage()),
        ];

        if (null !== $profile->secondaryLanguage()) {
            $lines[] = sprintf('Secondary language: %s.', $profile->secondaryLanguage());
        }

        $lines[] = match ($profile->technicalTermsPolicy()) {
            ShadowTechnicalLanguagePolicy::AlwaysOriginal => 'Keep technical terms in their original language.',
            ShadowTechnicalLanguagePolicy::AlwaysTranslate => 'Translate technical terms into the primary language.',
            ShadowTechnicalLanguagePolicy::OriginalWithExplanation => 'Keep technical terms original and add a short explanation.',
            ShadowTechnicalLanguagePolicy::Adaptive => 'Adapt technical term handling to the viewer context.',
        };

        $lines[] = sprintf(
            'Technical language baseline: %s.',
            $profile->technicalLanguage(),
        );

        $lines[] = sprintf(
            'Preferred pronunciation: %s.',
            $profile->pronunciation()->value,
        );

        if (null !== $profile->summaryLanguage()) {
            $lines[] = sprintf('Summaries should be in %s.', $profile->summaryLanguage());
        }

        return $lines;
    }

    /**
     * @return array<string, mixed>
     */
    public function applyOralCommand(ShadowLanguageProfile $profile, string $utterance): array
    {
        $normalized = mb_strtolower(trim($utterance));
        $updated = $profile;
        $applied = [];

        if (preg_match('/(?:uniquement en allemand|only german|nur deutsch)/u', $normalized)) {
            $updated = $updated->withPrimaryLanguage('de');
            $applied[] = 'primaryLanguage=de';
        }

        if (preg_match('/(?:explique en français|explain in french|auf französisch)/u', $normalized)) {
            $updated = $updated->withPrimaryLanguage('fr');
            $applied[] = 'primaryLanguage=fr';
        }

        if (preg_match('/(?:termes techniques.*anglais|technical terms.*english|fachbegriffe.*englisch)/u', $normalized)) {
            $updated = new ShadowLanguageProfile(
                $updated->primaryLanguage(),
                $updated->secondaryLanguage(),
                'en',
                ShadowTechnicalLanguagePolicy::AlwaysOriginal,
                $updated->pronunciation(),
                $updated->summaryLanguage(),
            );
            $applied[] = 'technicalTerms=english';
        }

        if (preg_match('/(?:acronymes en anglais|acronyms in english)/u', $normalized)) {
            $updated = $updated->withTechnicalTermsPolicy(ShadowTechnicalLanguagePolicy::AlwaysOriginal);
            $applied[] = 'acronyms=english';
        }

        if (preg_match('/(?:résumé.*allemand|summary in german)/u', $normalized)) {
            $updated = new ShadowLanguageProfile(
                $updated->primaryLanguage(),
                $updated->secondaryLanguage(),
                $updated->technicalLanguage(),
                $updated->technicalTermsPolicy(),
                $updated->pronunciation(),
                'de',
            );
            $applied[] = 'summaryLanguage=de';
        }

        if (preg_match('/(?:prononciation française|french pronunciation)/u', $normalized)) {
            $updated = $updated->withPronunciation(ShadowPronunciationPolicy::French);
            $applied[] = 'pronunciation=french';
        }

        return [
            'profile' => $updated,
            'applied' => $applied,
            'instructions' => $this->composeInstructionsFromProfile($updated),
        ];
    }

    /**
     * @return list<string>
     */
    private function composeInstructionsFromProfile(ShadowLanguageProfile $profile): array
    {
        return $this->composeInstructions(
            ShadowIdentityPreferences::default()->withLanguageProfile($profile),
        );
    }
}
