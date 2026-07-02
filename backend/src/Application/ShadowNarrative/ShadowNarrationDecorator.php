<?php

declare(strict_types=1);

namespace App\Application\ShadowNarrative;

use App\Domain\ShadowIdentity\ShadowNarrationStyle;

final class ShadowNarrationDecorator
{
    /**
     * @param list<string> $lines
     *
     * @return list<string>
     */
    public function decorate(array $lines, ShadowNarrationStyle $style): array
    {
        $lines[] = match ($style) {
            ShadowNarrationStyle::Documentary => 'Use documentary pacing: context, evidence, implication.',
            ShadowNarrationStyle::Professor => 'Structure as definition, principle, example, summary, application.',
            ShadowNarrationStyle::Coach => 'Use encouraging coaching language and a practical next step.',
            ShadowNarrationStyle::Debate => 'Present argument, counter-argument, nuance, and a question.',
            ShadowNarrationStyle::Socratic => 'Answer mostly with successive guiding questions.',
            ShadowNarrationStyle::Friendly => 'Use natural conversational language.',
            ShadowNarrationStyle::Storytelling => 'Frame the answer as a narrative arc.',
            default => 'Use clear neutral narration.',
        };

        return $lines;
    }
}
