<?php

declare(strict_types=1);

namespace App\Application\ShadowNarrative;

use App\Application\ShadowIdentity\ShadowLanguageComposer;
use App\Domain\ShadowIdentity\ShadowIdentityPreferences;
use App\Domain\ShadowIdentity\ShadowNarrationStyle;
use App\Domain\ShadowIdentity\ShadowVoicePersona;

final class ShadowAnswerEnricher
{
    public function __construct(
        private readonly ShadowStorytellingDecorator $storytellingDecorator,
        private readonly ShadowSpeechDecorator $speechDecorator,
        private readonly ShadowNarrationDecorator $narrationDecorator,
        private readonly ShadowLanguageComposer $languageComposer,
    ) {
    }

    /**
     * @param list<string> $baseLines
     *
     * @return list<string>
     */
    public function enrich(array $baseLines, ShadowIdentityPreferences $preferences): array
    {
        $lines = $this->speechDecorator->decorate($baseLines, $preferences);
        $lines = $this->narrationDecorator->decorate($lines, $preferences->narrationStyle());

        if (
            ShadowNarrationStyle::Storytelling === $preferences->narrationStyle()
            || ShadowVoicePersona::Storyteller === $preferences->persona()
        ) {
            $lines = $this->storytellingDecorator->decorate($lines);
        }

        return [...$lines, ...$this->languageComposer->composeInstructions($preferences)];
    }
}
