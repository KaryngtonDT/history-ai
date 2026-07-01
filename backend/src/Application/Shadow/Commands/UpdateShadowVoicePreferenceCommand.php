<?php

declare(strict_types=1);

namespace App\Application\Shadow\Commands;

use App\Domain\Shadow\ShadowVoicePreference;

final readonly class UpdateShadowVoicePreferenceCommand
{
    public function __construct(
        public string $videoId,
        public string $sessionId,
        public ShadowVoicePreference $voicePreference,
    ) {
    }
}
