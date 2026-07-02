<?php

declare(strict_types=1);

namespace App\Application\ShadowVoice;

use App\Domain\ShadowIdentity\ShadowHumorLevel;
use App\Domain\ShadowIdentity\ShadowVoiceProfile;

final class ShadowVoiceStudio
{
    public function __construct(private readonly ShadowVoicePresetMapper $presetMapper)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function library(): array
    {
        return [
            'engines' => ShadowVoiceCatalog::engines(),
            'voices' => array_map(
                static fn (ShadowVoiceDefinition $voice): array => $voice->toArray(),
                ShadowVoiceCatalog::all(),
            ),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function collections(): array
    {
        $collections = [];

        foreach (ShadowVoiceCollection::cases() as $collection) {
            $collections[] = [
                'id' => $collection->value,
                'label' => $collection->label(),
                'description' => $collection->description(),
                'voiceIds' => array_map(
                    static fn (ShadowVoiceDefinition $voice): string => $voice->id(),
                    ShadowVoiceCatalog::forCollection($collection),
                ),
            ];
        }

        return $collections;
    }

    /**
     * @return list<array<string, string>>
     */
    public function presets(): array
    {
        return array_map(
            static fn (ShadowVoicePreset $preset): array => [
                'id' => $preset->value,
                'label' => $preset->label(),
            ],
            ShadowVoicePreset::cases(),
        );
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    public function preview(string $voiceId, array $parameters = []): array
    {
        $voice = ShadowVoiceCatalog::findById($voiceId) ?? ShadowVoiceCatalog::findById('browser-default');
        $profile = $this->buildProfile($voiceId, $parameters);

        return [
            'voiceId' => $voice?->id() ?? 'browser-default',
            'engine' => $profile->engine(),
            'text' => $voice?->previewText() ?? 'Hello, I am Shadow.',
            'language' => $voice?->supportedLanguages()[0] ?? 'en',
            'parameters' => [
                'speed' => $profile->speed(),
                'pitch' => $profile->pitch(),
                'warmth' => $profile->warmth(),
                'energy' => $profile->energy(),
                'emotion' => $profile->emotion(),
                'pauses' => $profile->pauses(),
                'expressiveness' => $profile->expressiveness(),
                'thinkingPauses' => $profile->thinkingPausesEnabled(),
                'humor' => $profile->humor()->value,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function applyPreset(ShadowVoicePreset $preset): array
    {
        $mapped = $this->presetMapper->apply($preset);

        return [
            'preset' => $preset->value,
            'persona' => $mapped['persona']->value,
            'voiceProfile' => $this->profileToArray($mapped['voiceProfile']),
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function buildProfile(string $voiceId, array $parameters): ShadowVoiceProfile
    {
        $voice = ShadowVoiceCatalog::findById($voiceId);
        $profile = ShadowVoiceProfile::default();

        if (null !== $voice) {
            $profile = $profile->withVoice($voice->id(), $voice->engine()->value);
        }

        if (isset($parameters['speed']) && is_numeric($parameters['speed'])) {
            $profile = $profile->withSpeed((float) $parameters['speed']);
        }

        if (isset($parameters['humor']) && is_string($parameters['humor'])) {
            $humor = ShadowHumorLevel::tryFrom($parameters['humor']);

            if (null !== $humor) {
                $profile = $profile->withHumor($humor);
            }
        }

        return $profile;
    }

    /**
     * @return array<string, mixed>
     */
    private function profileToArray(ShadowVoiceProfile $profile): array
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
}
