<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Review;

use App\Domain\Review\LipSyncStrengthPreference;
use App\Domain\Review\RenderingPresetPreference;
use App\Domain\Review\TranslationStylePreference;
use App\Domain\Review\UserPreferenceProfile;
use App\Domain\Review\VoiceStabilityPreference;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_preference_profiles')]
class UserPreferenceProfileRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 32)]
    private string $id = 'default';

    #[ORM\Column(name: 'translation_style', type: Types::STRING, length: 32)]
    private string $translationStyle;

    #[ORM\Column(name: 'voice_stability', type: Types::STRING, length: 32)]
    private string $voiceStability;

    #[ORM\Column(name: 'rendering_preset', type: Types::STRING, length: 32)]
    private string $renderingPreset;

    #[ORM\Column(name: 'lip_sync_strength', type: Types::STRING, length: 32)]
    private string $lipSyncStrength;

    private function __construct()
    {
    }

    public static function fromDomain(UserPreferenceProfile $profile): self
    {
        $record = new self();
        $record->translationStyle = $profile->translationStyle()->value;
        $record->voiceStability = $profile->voiceStability()->value;
        $record->renderingPreset = $profile->renderingPreset()->value;
        $record->lipSyncStrength = $profile->lipSyncStrength()->value;

        return $record;
    }

    public function updateFromDomain(UserPreferenceProfile $profile): void
    {
        $this->translationStyle = $profile->translationStyle()->value;
        $this->voiceStability = $profile->voiceStability()->value;
        $this->renderingPreset = $profile->renderingPreset()->value;
        $this->lipSyncStrength = $profile->lipSyncStrength()->value;
    }

    public function toDomain(): UserPreferenceProfile
    {
        return UserPreferenceProfile::create(
            TranslationStylePreference::from($this->translationStyle),
            VoiceStabilityPreference::from($this->voiceStability),
            RenderingPresetPreference::from($this->renderingPreset),
            LipSyncStrengthPreference::from($this->lipSyncStrength),
        );
    }
}
