<?php

declare(strict_types=1);

namespace App\Application\Shadow;

use App\Domain\Shadow\ShadowVoiceLanguage;
use App\Domain\Shadow\ShadowVoiceMode;
use App\Domain\Shadow\ShadowVoicePreference;

final class ShadowVoicePreferenceMapper
{
    /**
     * @param array<string, mixed> $payload
     */
    public function fromArray(array $payload, ShadowVoicePreference $current): ShadowVoicePreference
    {
        $preference = $current;

        if (isset($payload['mode']) && is_string($payload['mode'])) {
            $mode = ShadowVoiceMode::tryFrom($payload['mode']);

            if (null !== $mode) {
                $preference = $preference->withMode(
                    $mode,
                    isset($payload['manualLanguage']) && is_string($payload['manualLanguage'])
                        ? ShadowVoiceLanguage::fromString($payload['manualLanguage'])
                        : $current->manualLanguage(),
                );
            }
        }

        if (isset($payload['manualLanguage']) && is_string($payload['manualLanguage'])) {
            $preference = $preference->withManualLanguage(
                ShadowVoiceLanguage::fromString($payload['manualLanguage']),
            );
        }

        return $preference;
    }
}
