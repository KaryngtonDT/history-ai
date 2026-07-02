<?php

declare(strict_types=1);

namespace App\Application\ShadowConfiguration;

use App\Domain\ShadowIdentity\ShadowAnswerStyle;
use App\Domain\ShadowIdentity\ShadowConversationStyle;
use App\Domain\ShadowIdentity\ShadowHumorLevel;
use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Domain\ShadowIdentity\ShadowIdentityPreferences;
use App\Domain\ShadowIdentity\ShadowLanguageProfile;
use App\Domain\ShadowIdentity\ShadowTechnicalLanguagePolicy;
use App\Domain\ShadowIdentity\ShadowVoicePersona;
use App\Domain\ShadowIdentity\ShadowVoiceProfile;

final class ShadowConfigurationExecutor
{
    public function apply(ShadowIdentity $identity, ShadowConfigurationDetection $detection): ShadowIdentity
    {
        return match ($detection->intent) {
            ShadowConfigurationIntent::ChangeSpeed => $this->applySpeed($identity, $detection),
            ShadowConfigurationIntent::ChangePersona => $this->applyPersona($identity, $detection),
            ShadowConfigurationIntent::ChangeChallenge => $this->applyChallenge($identity, $detection),
            ShadowConfigurationIntent::UpdateConversationStyle => $this->applyConversationStyle($identity, $detection),
            ShadowConfigurationIntent::UpdateLearningStyle => $this->applyLearningStyle($identity, $detection),
            ShadowConfigurationIntent::ForgetPreference => $identity->forgetPreference(
                is_string($detection->parameters['key'] ?? null) ? $detection->parameters['key'] : 'last',
            ),
            ShadowConfigurationIntent::ResetProfile => $identity->reset(),
            ShadowConfigurationIntent::ChangeLanguage => $this->applyLanguage($identity, $detection),
            ShadowConfigurationIntent::ChangeTechnicalTerms => $this->applyTechnicalTerms($identity, $detection),
            ShadowConfigurationIntent::ChangeAnswerLength => $this->applyAnswerLength($identity, $detection),
            ShadowConfigurationIntent::ChangeHumor => $this->applyHumor($identity, $detection),
            default => $identity,
        };
    }

    public function previewChange(
        ShadowIdentityPreferences $preferences,
        ShadowConfigurationDetection $detection,
    ): array {
        return match ($detection->intent) {
            ShadowConfigurationIntent::ChangeSpeed => [
                'field' => 'speechSpeed',
                'from' => $preferences->voiceProfile()->speed(),
                'to' => $this->nextSpeed($preferences->voiceProfile(), $detection),
            ],
            ShadowConfigurationIntent::ChangePersona => [
                'field' => 'persona',
                'from' => $preferences->persona()->value,
                'to' => $this->resolvePersona($detection)->value,
            ],
            ShadowConfigurationIntent::ChangeChallenge => [
                'field' => 'challengeLevel',
                'from' => $preferences->challengeProfile()->level(),
                'to' => $this->nextChallengeLevel($preferences, $detection),
            ],
            ShadowConfigurationIntent::UpdateConversationStyle => [
                'field' => 'conversationStyle',
                'from' => $preferences->conversationStyle()->value,
                'to' => ShadowConversationStyle::Friendly->value,
            ],
            ShadowConfigurationIntent::UpdateLearningStyle => [
                'field' => 'examples',
                'from' => $preferences->examplesLevel(),
                'to' => 9,
            ],
            ShadowConfigurationIntent::ResetProfile => [
                'field' => 'profile',
                'from' => 'custom',
                'to' => 'default',
            ],
            default => [
                'field' => $detection->intent->value,
                'from' => null,
                'to' => $detection->parameters,
            ],
        };
    }

    private function applySpeed(ShadowIdentity $identity, ShadowConfigurationDetection $detection): ShadowIdentity
    {
        $speed = $this->nextSpeed($identity->preferences()->voiceProfile(), $detection);

        return $identity->withVoiceProfile(
            $identity->preferences()->voiceProfile()->withSpeed($speed),
        );
    }

    private function applyPersona(ShadowIdentity $identity, ShadowConfigurationDetection $detection): ShadowIdentity
    {
        return $identity->withPersona($this->resolvePersona($detection));
    }

    private function applyChallenge(ShadowIdentity $identity, ShadowConfigurationDetection $detection): ShadowIdentity
    {
        $level = $this->nextChallengeLevel($identity->preferences(), $detection);

        return $identity->withChallengeLevel($level);
    }

    private function applyConversationStyle(
        ShadowIdentity $identity,
        ShadowConfigurationDetection $detection,
    ): ShadowIdentity {
        $style = ShadowConversationStyle::tryFrom(
            is_string($detection->parameters['style'] ?? null) ? $detection->parameters['style'] : 'friendly',
        ) ?? ShadowConversationStyle::Friendly;

        return $identity->applyPreferences(
            $identity->preferences()->withConversationStyle($style),
            sprintf('Conversation style → %s', $style->value),
            'conversational',
        );
    }

    private function applyLearningStyle(ShadowIdentity $identity, ShadowConfigurationDetection $detection): ShadowIdentity
    {
        $examples = ('very_high' === ($detection->parameters['examples'] ?? null)) ? 9 : 7;

        return $identity->applyPreferences(
            $identity->preferences()->withExamplesLevel($examples),
            'Examples → very high',
            'conversational',
        );
    }

    private function applyLanguage(ShadowIdentity $identity, ShadowConfigurationDetection $detection): ShadowIdentity
    {
        $language = is_string($detection->parameters['primaryLanguage'] ?? null)
            ? $detection->parameters['primaryLanguage']
            : 'en';

        return $identity->applyPreferences(
            $identity->preferences()->withLanguageProfile(
                $identity->preferences()->languageProfile()->withPrimaryLanguage($language),
            ),
            sprintf('Primary language → %s', $language),
            'conversational',
        );
    }

    private function applyTechnicalTerms(
        ShadowIdentity $identity,
        ShadowConfigurationDetection $detection,
    ): ShadowIdentity {
        $policy = ShadowTechnicalLanguagePolicy::tryFrom(
            is_string($detection->parameters['policy'] ?? null) ? $detection->parameters['policy'] : 'always_original',
        ) ?? ShadowTechnicalLanguagePolicy::AlwaysOriginal;

        $languageProfile = $identity->preferences()->languageProfile()->withTechnicalTermsPolicy($policy);

        if (is_string($detection->parameters['technicalLanguage'] ?? null)) {
            $languageProfile = new ShadowLanguageProfile(
                $languageProfile->primaryLanguage(),
                $languageProfile->secondaryLanguage(),
                $detection->parameters['technicalLanguage'],
                $languageProfile->technicalTermsPolicy(),
                $languageProfile->pronunciation(),
                $languageProfile->summaryLanguage(),
            );
        }

        return $identity->applyPreferences(
            $identity->preferences()->withLanguageProfile($languageProfile),
            'Technical terms policy updated',
            'conversational',
        );
    }

    private function applyAnswerLength(ShadowIdentity $identity, ShadowConfigurationDetection $detection): ShadowIdentity
    {
        $style = ShadowAnswerStyle::tryFrom(
            is_string($detection->parameters['answerStyle'] ?? null) ? $detection->parameters['answerStyle'] : 'short',
        ) ?? ShadowAnswerStyle::Short;

        $updated = new ShadowIdentityPreferences(
            $identity->preferences()->persona(),
            $identity->preferences()->personaTraits(),
            $identity->preferences()->voiceProfile(),
            $identity->preferences()->conversationStyle(),
            $identity->preferences()->teachingStyle(),
            $identity->preferences()->narrationStyle(),
            $identity->preferences()->languageProfile(),
            $style,
            $identity->preferences()->challengeProfile(),
            $identity->preferences()->memoryPolicy(),
            $identity->preferences()->interruptionPolicy(),
            $identity->preferences()->thinkingStyle(),
            $identity->preferences()->humorLevel(),
            $identity->preferences()->curiosity(),
            $identity->preferences()->examplesLevel(),
            $identity->preferences()->storiesLevel(),
            $identity->preferences()->debateLevel(),
        );

        return $identity->applyPreferences($updated, 'Answer length updated', 'conversational');
    }

    private function applyHumor(ShadowIdentity $identity, ShadowConfigurationDetection $detection): ShadowIdentity
    {
        $humor = ShadowHumorLevel::tryFrom(
            is_string($detection->parameters['humor'] ?? null) ? $detection->parameters['humor'] : 'high',
        ) ?? ShadowHumorLevel::High;

        $voice = $identity->preferences()->voiceProfile()->withHumor($humor);
        $updated = $identity->preferences()->withVoiceProfile($voice);

        return $identity->applyPreferences($updated, 'Humor level updated', 'conversational');
    }

    private function nextSpeed(ShadowVoiceProfile $profile, ShadowConfigurationDetection $detection): float
    {
        $delta = ('decrease' === ($detection->parameters['direction'] ?? null)) ? -0.1 : 0.1;

        return max(0.5, min(2.0, round($profile->speed() + $delta, 2)));
    }

    private function nextChallengeLevel(
        ShadowIdentityPreferences $preferences,
        ShadowConfigurationDetection $detection,
    ): int {
        $profile = $preferences->challengeProfile();

        return ('decrease' === ($detection->parameters['direction'] ?? null))
            ? $profile->decrease()->level()
            : $profile->increase()->level();
    }

    private function resolvePersona(ShadowConfigurationDetection $detection): ShadowVoicePersona
    {
        $value = is_string($detection->parameters['persona'] ?? null)
            ? $detection->parameters['persona']
            : 'teacher';

        return match ($value) {
            'storyteller' => ShadowVoicePersona::Storyteller,
            'professor' => ShadowVoicePersona::Professor,
            'coach' => ShadowVoicePersona::Coach,
            'friendly_companion' => ShadowVoicePersona::FriendlyCompanion,
            default => ShadowVoicePersona::Teacher,
        };
    }
}
