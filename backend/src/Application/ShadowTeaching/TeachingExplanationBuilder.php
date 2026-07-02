<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowTeaching\TeachingVoiceMode;

final class TeachingExplanationBuilder
{
    /** @return list<string> */
    public function voiceLines(TeachingVoiceMode $mode): array
    {
        return match ($mode) {
            TeachingVoiceMode::Professor => [
                'Teaching voice: Professor — provide structured, detailed explanations.',
            ],
            TeachingVoiceMode::Coach => [
                'Teaching voice: Coach — encourage progress and give practical next steps.',
            ],
            TeachingVoiceMode::Storyteller => [
                'Teaching voice: Storyteller — use analogies and narrative examples.',
            ],
            TeachingVoiceMode::Examiner => [
                'Teaching voice: Examiner — ask verification questions before continuing.',
            ],
            TeachingVoiceMode::Socratic => [
                'Teaching voice: Socratic — guide with questions before giving answers.',
            ],
        };
    }
}
