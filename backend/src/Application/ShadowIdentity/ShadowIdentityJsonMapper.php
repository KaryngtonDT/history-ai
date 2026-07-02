<?php

declare(strict_types=1);

namespace App\Application\ShadowIdentity;

use App\Domain\ShadowIdentity\ShadowAnswerStyle;
use App\Domain\ShadowIdentity\ShadowConversationStyle;
use App\Domain\ShadowIdentity\ShadowHumorLevel;
use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Domain\ShadowIdentity\ShadowIdentitySnapshot;
use App\Domain\ShadowIdentity\ShadowLanguageProfile;
use App\Domain\ShadowIdentity\ShadowNarrationStyle;
use App\Domain\ShadowIdentity\ShadowPronunciationPolicy;
use App\Domain\ShadowIdentity\ShadowTeachingStyle;
use App\Domain\ShadowIdentity\ShadowTechnicalLanguagePolicy;
use App\Domain\ShadowIdentity\ShadowThinkingStyle;
use App\Domain\ShadowIdentity\ShadowVoicePersona;
use App\Domain\ShadowIdentity\ShadowVoiceProfile;

final class ShadowIdentityJsonMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(ShadowIdentity $identity): array
    {
        $preferences = $identity->preferences();

        return [
            'id' => $identity->id()->value,
            'scopeKey' => $identity->scopeKey(),
            'preferences' => [
                'persona' => $preferences->persona()->value,
                'personaTraits' => [
                    'tone' => $preferences->personaTraits()->tone,
                    'energy' => $preferences->personaTraits()->energy,
                    'warmth' => $preferences->personaTraits()->warmth,
                    'verbosity' => $preferences->personaTraits()->verbosity,
                    'examples' => $preferences->personaTraits()->examples,
                    'analogies' => $preferences->personaTraits()->analogies,
                    'challenge' => $preferences->personaTraits()->challenge,
                    'humor' => $preferences->personaTraits()->humor,
                    'emotion' => $preferences->personaTraits()->emotion,
                    'storytelling' => $preferences->personaTraits()->storytelling,
                    'thinkingStyle' => $preferences->personaTraits()->thinkingStyle->value,
                    'interactionRhythm' => $preferences->personaTraits()->interactionRhythm,
                ],
                'voiceProfile' => $this->voiceProfileToArray($preferences->voiceProfile()),
                'conversationStyle' => $preferences->conversationStyle()->value,
                'teachingStyle' => $preferences->teachingStyle()->value,
                'narrationStyle' => $preferences->narrationStyle()->value,
                'languageProfile' => $this->languageProfileToArray($preferences->languageProfile()),
                'answerStyle' => $preferences->answerStyle()->value,
                'challengeLevel' => $preferences->challengeProfile()->level(),
                'memoryPolicy' => [
                    'rememberPreferences' => $preferences->memoryPolicy()->rememberPreferences(),
                    'rememberConversationContext' => $preferences->memoryPolicy()->rememberConversationContext(),
                    'knownSkills' => $preferences->memoryPolicy()->knownSkills(),
                    'unknownSkills' => $preferences->memoryPolicy()->unknownSkills(),
                    'goals' => $preferences->memoryPolicy()->goals(),
                    'interests' => $preferences->memoryPolicy()->interests(),
                ],
                'interruptionPolicy' => [
                    'allowInterruptions' => $preferences->interruptionPolicy()->allowInterruptions(),
                    'thinkingPauses' => $preferences->interruptionPolicy()->thinkingPauses(),
                    'maxInterruptionsPerMinute' => $preferences->interruptionPolicy()->maxInterruptionsPerMinute(),
                ],
                'thinkingStyle' => $preferences->thinkingStyle()->value,
                'humorLevel' => $preferences->humorLevel()->value,
                'curiosity' => $preferences->curiosity(),
                'examplesLevel' => $preferences->examplesLevel(),
                'storiesLevel' => $preferences->storiesLevel(),
                'debateLevel' => $preferences->debateLevel(),
            ],
            'dna' => [
                'curiosity' => $preferences->curiosity(),
                'examples' => $preferences->examplesLevel(),
                'stories' => $preferences->storiesLevel(),
                'debate' => $preferences->debateLevel(),
                'challenge' => $preferences->challengeProfile()->level() * 2,
                'humor' => $preferences->humorLevel()->scale(),
            ],
            'history' => array_map(
                fn (ShadowIdentitySnapshot $snapshot): array => [
                    'id' => $snapshot->id()->value,
                    'label' => $snapshot->label(),
                    'source' => $snapshot->source(),
                    'recordedAt' => $snapshot->recordedAt()->format(DATE_ATOM),
                ],
                $identity->history()->recent(20)->all(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function voiceProfileToArray(ShadowVoiceProfile $profile): array
    {
        return [
            'voiceId' => $profile->voiceId(),
            'engine' => $profile->engine(),
            'speed' => $profile->speed(),
            'pitch' => $profile->pitch(),
            'warmth' => $profile->warmth(),
            'energy' => $profile->energy(),
            'emotion' => $profile->emotion(),
            'pauses' => $profile->pauses(),
            'expressiveness' => $profile->expressiveness(),
            'thinkingPauses' => $profile->thinkingPausesEnabled(),
            'humor' => $profile->humor()->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function languageProfileToArray(ShadowLanguageProfile $profile): array
    {
        return [
            'primaryLanguage' => $profile->primaryLanguage(),
            'secondaryLanguage' => $profile->secondaryLanguage(),
            'technicalLanguage' => $profile->technicalLanguage(),
            'technicalTermsPolicy' => $profile->technicalTermsPolicy()->value,
            'pronunciation' => $profile->pronunciation()->value,
            'summaryLanguage' => $profile->summaryLanguage(),
        ];
    }
}
