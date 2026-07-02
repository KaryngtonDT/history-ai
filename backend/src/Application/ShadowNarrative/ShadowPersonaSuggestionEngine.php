<?php

declare(strict_types=1);

namespace App\Application\ShadowNarrative;

use App\Domain\ShadowIdentity\ShadowVoicePersona;

final class ShadowPersonaSuggestionEngine
{
    /**
     * @return array{persona: string, reason: string}|null
     */
    public function suggest(?string $contentCategory): ?array
    {
        return match ($contentCategory) {
            'history', 'documentary' => [
                'persona' => ShadowVoicePersona::Storyteller->value,
                'reason' => 'History content is often more engaging with Storyteller mode.',
            ],
            'technical', 'engineering', 'science' => [
                'persona' => ShadowVoicePersona::TechnicalExpert->value,
                'reason' => 'Technical content is clearer with Technical Expert mode.',
            ],
            'lecture', 'academic' => [
                'persona' => ShadowVoicePersona::Professor->value,
                'reason' => 'Lecture content fits Professor mode well.',
            ],
            default => null,
        };
    }
}
