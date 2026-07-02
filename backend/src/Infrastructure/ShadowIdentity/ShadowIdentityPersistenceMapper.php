<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowIdentity;

use App\Domain\ShadowIdentity\ShadowAnswerStyle;
use App\Domain\ShadowIdentity\ShadowConversationStyle;
use App\Domain\ShadowIdentity\ShadowHumorLevel;
use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Domain\ShadowIdentity\ShadowIdentityId;
use App\Domain\ShadowIdentity\ShadowIdentityPreferences;
use App\Domain\ShadowIdentity\ShadowIdentityRepositoryInterface;
use App\Domain\ShadowIdentity\ShadowIdentitySnapshot;
use App\Domain\ShadowIdentity\ShadowIdentitySnapshotCollection;
use App\Domain\ShadowIdentity\ShadowInterruptionPolicy;
use App\Domain\ShadowIdentity\ShadowLanguageProfile;
use App\Domain\ShadowIdentity\ShadowMemoryPolicy;
use App\Domain\ShadowIdentity\ShadowNarrationStyle;
use App\Domain\ShadowIdentity\ShadowPersonaTraits;
use App\Domain\ShadowIdentity\ShadowPronunciationPolicy;
use App\Domain\ShadowIdentity\ShadowTeachingStyle;
use App\Domain\ShadowIdentity\ShadowTechnicalLanguagePolicy;
use App\Domain\ShadowIdentity\ShadowThinkingStyle;
use App\Domain\ShadowIdentity\ShadowVoicePersona;
use App\Domain\ShadowIdentity\ShadowVoiceProfile;
use App\Domain\ShadowIdentity\Exception\InvalidShadowIdentityException;
use App\Infrastructure\Storage\JsonFileStore;
use JsonException;

final class ShadowIdentityPersistenceMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(ShadowIdentity $identity): array
    {
        return [
            'id' => $identity->id()->value,
            'scopeKey' => $identity->scopeKey(),
            'preferences' => $this->preferencesToArray($identity->preferences()),
            'history' => array_map(
                fn (ShadowIdentitySnapshot $snapshot): array => [
                    'id' => $snapshot->id()->value,
                    'label' => $snapshot->label(),
                    'source' => $snapshot->source(),
                    'recordedAt' => $snapshot->recordedAt()->format(DATE_ATOM),
                    'preferences' => $this->preferencesToArray($snapshot->preferences()),
                ],
                $identity->history()->all(),
            ),
        ];
    }

    public function toJson(ShadowIdentity $identity): string
    {
        return json_encode($this->toArray($identity), JSON_THROW_ON_ERROR);
    }

    public function fromJson(string $json): ShadowIdentity
    {
        try {
            /** @var mixed $decoded */
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowIdentityException('Stored shadow identity is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidShadowIdentityException('Stored shadow identity must be a JSON object.');
        }

        $idValue = is_string($decoded['id'] ?? null) ? $decoded['id'] : null;
        $scopeKey = is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default';

        if (null === $idValue) {
            throw new InvalidShadowIdentityException('Stored shadow identity is missing id.');
        }

        $preferences = $this->preferencesFromArray(
            is_array($decoded['preferences'] ?? null) ? $decoded['preferences'] : [],
        );

        $historyItems = is_array($decoded['history'] ?? null) ? $decoded['history'] : [];
        $snapshots = [];

        foreach ($historyItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $snapshotId = is_string($item['id'] ?? null) ? $item['id'] : ShadowIdentityId::generate()->value;
            $label = is_string($item['label'] ?? null) ? $item['label'] : 'Snapshot';
            $source = is_string($item['source'] ?? null) ? $item['source'] : 'system';
            $recordedAtValue = is_string($item['recordedAt'] ?? null) ? $item['recordedAt'] : null;
            $recordedAt = $recordedAtValue
                ? \DateTimeImmutable::createFromFormat(DATE_ATOM, $recordedAtValue) ?: new \DateTimeImmutable()
                : new \DateTimeImmutable();

            $snapshots[] = new ShadowIdentitySnapshot(
                new ShadowIdentityId($snapshotId),
                $recordedAt,
                $label,
                $this->preferencesFromArray(is_array($item['preferences'] ?? null) ? $item['preferences'] : []),
                $source,
            );
        }

        if ([] === $snapshots) {
            $snapshots[] = ShadowIdentitySnapshot::capture($preferences, 'Initial profile', 'system');
        }

        return new ShadowIdentity(
            new ShadowIdentityId($idValue),
            $scopeKey,
            $preferences,
            new ShadowIdentitySnapshotCollection($snapshots),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function preferencesToArray(ShadowIdentityPreferences $preferences): array
    {
        $traits = $preferences->personaTraits();
        $voice = $preferences->voiceProfile();
        $language = $preferences->languageProfile();
        $memory = $preferences->memoryPolicy();
        $interruption = $preferences->interruptionPolicy();

        return [
            'persona' => $preferences->persona()->value,
            'personaTraits' => [
                'tone' => $traits->tone,
                'energy' => $traits->energy,
                'warmth' => $traits->warmth,
                'verbosity' => $traits->verbosity,
                'examples' => $traits->examples,
                'analogies' => $traits->analogies,
                'challenge' => $traits->challenge,
                'humor' => $traits->humor,
                'emotion' => $traits->emotion,
                'storytelling' => $traits->storytelling,
                'thinkingStyle' => $traits->thinkingStyle->value,
                'interactionRhythm' => $traits->interactionRhythm,
            ],
            'voiceProfile' => [
                'voiceId' => $voice->voiceId(),
                'engine' => $voice->engine(),
                'speed' => $voice->speed(),
                'pitch' => $voice->pitch(),
                'warmth' => $voice->warmth(),
                'energy' => $voice->energy(),
                'emotion' => $voice->emotion(),
                'pauses' => $voice->pauses(),
                'expressiveness' => $voice->expressiveness(),
                'thinkingPauses' => $voice->thinkingPausesEnabled(),
                'humor' => $voice->humor()->value,
            ],
            'conversationStyle' => $preferences->conversationStyle()->value,
            'teachingStyle' => $preferences->teachingStyle()->value,
            'narrationStyle' => $preferences->narrationStyle()->value,
            'languageProfile' => [
                'primaryLanguage' => $language->primaryLanguage(),
                'secondaryLanguage' => $language->secondaryLanguage(),
                'technicalLanguage' => $language->technicalLanguage(),
                'technicalTermsPolicy' => $language->technicalTermsPolicy()->value,
                'pronunciation' => $language->pronunciation()->value,
                'summaryLanguage' => $language->summaryLanguage(),
            ],
            'answerStyle' => $preferences->answerStyle()->value,
            'challengeLevel' => $preferences->challengeProfile()->level(),
            'memoryPolicy' => [
                'rememberPreferences' => $memory->rememberPreferences(),
                'rememberConversationContext' => $memory->rememberConversationContext(),
                'knownSkills' => $memory->knownSkills(),
                'unknownSkills' => $memory->unknownSkills(),
                'goals' => $memory->goals(),
                'interests' => $memory->interests(),
            ],
            'interruptionPolicy' => [
                'allowInterruptions' => $interruption->allowInterruptions(),
                'thinkingPauses' => $interruption->thinkingPauses(),
                'maxInterruptionsPerMinute' => $interruption->maxInterruptionsPerMinute(),
            ],
            'thinkingStyle' => $preferences->thinkingStyle()->value,
            'humorLevel' => $preferences->humorLevel()->value,
            'curiosity' => $preferences->curiosity(),
            'examplesLevel' => $preferences->examplesLevel(),
            'storiesLevel' => $preferences->storiesLevel(),
            'debateLevel' => $preferences->debateLevel(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function preferencesFromArray(array $data): ShadowIdentityPreferences
    {
        if ([] === $data) {
            return ShadowIdentityPreferences::default();
        }

        $persona = ShadowVoicePersona::tryFrom((string) ($data['persona'] ?? ''))
            ?? ShadowVoicePersona::Teacher;
        $traitsData = is_array($data['personaTraits'] ?? null) ? $data['personaTraits'] : [];
        $traits = new ShadowPersonaTraits(
            tone: (int) ($traitsData['tone'] ?? $persona->traits()->tone),
            energy: (int) ($traitsData['energy'] ?? $persona->traits()->energy),
            warmth: (int) ($traitsData['warmth'] ?? $persona->traits()->warmth),
            verbosity: (int) ($traitsData['verbosity'] ?? $persona->traits()->verbosity),
            examples: (int) ($traitsData['examples'] ?? $persona->traits()->examples),
            analogies: (int) ($traitsData['analogies'] ?? $persona->traits()->analogies),
            challenge: (int) ($traitsData['challenge'] ?? $persona->traits()->challenge),
            humor: (int) ($traitsData['humor'] ?? $persona->traits()->humor),
            emotion: (int) ($traitsData['emotion'] ?? $persona->traits()->emotion),
            storytelling: (int) ($traitsData['storytelling'] ?? $persona->traits()->storytelling),
            thinkingStyle: ShadowThinkingStyle::tryFrom((string) ($traitsData['thinkingStyle'] ?? ''))
                ?? $persona->traits()->thinkingStyle,
            interactionRhythm: (int) ($traitsData['interactionRhythm'] ?? $persona->traits()->interactionRhythm),
        );

        $voiceData = is_array($data['voiceProfile'] ?? null) ? $data['voiceProfile'] : [];
        $voice = new ShadowVoiceProfile(
            voiceId: (string) ($voiceData['voiceId'] ?? 'browser-default'),
            engine: (string) ($voiceData['engine'] ?? 'browser_tts'),
            speed: (float) ($voiceData['speed'] ?? 1.0),
            pitch: (float) ($voiceData['pitch'] ?? 1.0),
            warmth: (int) ($voiceData['warmth'] ?? 6),
            energy: (int) ($voiceData['energy'] ?? 6),
            emotion: (int) ($voiceData['emotion'] ?? 5),
            pauses: (int) ($voiceData['pauses'] ?? 5),
            expressiveness: (int) ($voiceData['expressiveness'] ?? 6),
            thinkingPauses: (bool) ($voiceData['thinkingPauses'] ?? true),
            humor: ShadowHumorLevel::tryFrom((string) ($voiceData['humor'] ?? '')) ?? ShadowHumorLevel::Low,
        );

        $languageData = is_array($data['languageProfile'] ?? null) ? $data['languageProfile'] : [];
        $language = new ShadowLanguageProfile(
            primaryLanguage: (string) ($languageData['primaryLanguage'] ?? 'en'),
            secondaryLanguage: is_string($languageData['secondaryLanguage'] ?? null)
                ? $languageData['secondaryLanguage']
                : null,
            technicalLanguage: (string) ($languageData['technicalLanguage'] ?? 'en'),
            technicalTermsPolicy: ShadowTechnicalLanguagePolicy::tryFrom(
                (string) ($languageData['technicalTermsPolicy'] ?? ''),
            ) ?? ShadowTechnicalLanguagePolicy::Adaptive,
            pronunciation: ShadowPronunciationPolicy::tryFrom((string) ($languageData['pronunciation'] ?? ''))
                ?? ShadowPronunciationPolicy::American,
            summaryLanguage: is_string($languageData['summaryLanguage'] ?? null)
                ? $languageData['summaryLanguage']
                : null,
        );

        $memoryData = is_array($data['memoryPolicy'] ?? null) ? $data['memoryPolicy'] : [];
        $memory = new ShadowMemoryPolicy(
            rememberPreferences: (bool) ($memoryData['rememberPreferences'] ?? true),
            rememberConversationContext: (bool) ($memoryData['rememberConversationContext'] ?? true),
            knownSkills: is_array($memoryData['knownSkills'] ?? null)
                ? array_values(array_filter($memoryData['knownSkills'], is_string(...)))
                : [],
            unknownSkills: is_array($memoryData['unknownSkills'] ?? null)
                ? array_values(array_filter($memoryData['unknownSkills'], is_string(...)))
                : [],
            goals: is_array($memoryData['goals'] ?? null)
                ? array_values(array_filter($memoryData['goals'], is_string(...)))
                : [],
            interests: is_array($memoryData['interests'] ?? null)
                ? array_values(array_filter($memoryData['interests'], is_string(...)))
                : [],
        );

        $interruptionData = is_array($data['interruptionPolicy'] ?? null) ? $data['interruptionPolicy'] : [];

        return new ShadowIdentityPreferences(
            persona: $persona,
            personaTraits: $traits,
            voiceProfile: $voice,
            conversationStyle: ShadowConversationStyle::tryFrom((string) ($data['conversationStyle'] ?? ''))
                ?? $persona->defaultConversationStyle(),
            teachingStyle: ShadowTeachingStyle::tryFrom((string) ($data['teachingStyle'] ?? ''))
                ?? $persona->defaultTeachingStyle(),
            narrationStyle: ShadowNarrationStyle::tryFrom((string) ($data['narrationStyle'] ?? ''))
                ?? $persona->defaultNarrationStyle(),
            languageProfile: $language,
            answerStyle: ShadowAnswerStyle::tryFrom((string) ($data['answerStyle'] ?? ''))
                ?? ShadowAnswerStyle::Detailed,
            challengeProfile: new \App\Domain\ShadowIdentity\ShadowChallengeProfile(
                (int) ($data['challengeLevel'] ?? 3),
            ),
            memoryPolicy: $memory,
            interruptionPolicy: new ShadowInterruptionPolicy(
                allowInterruptions: (bool) ($interruptionData['allowInterruptions'] ?? true),
                thinkingPauses: (bool) ($interruptionData['thinkingPauses'] ?? true),
                maxInterruptionsPerMinute: (int) ($interruptionData['maxInterruptionsPerMinute'] ?? 4),
            ),
            thinkingStyle: ShadowThinkingStyle::tryFrom((string) ($data['thinkingStyle'] ?? ''))
                ?? $traits->thinkingStyle,
            humorLevel: ShadowHumorLevel::tryFrom((string) ($data['humorLevel'] ?? '')) ?? ShadowHumorLevel::Low,
            curiosity: (int) ($data['curiosity'] ?? 6),
            examplesLevel: (int) ($data['examplesLevel'] ?? $traits->examples),
            storiesLevel: (int) ($data['storiesLevel'] ?? $traits->storytelling),
            debateLevel: (int) ($data['debateLevel'] ?? 4),
        );
    }
}
