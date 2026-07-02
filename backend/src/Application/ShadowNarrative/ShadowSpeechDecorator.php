<?php

declare(strict_types=1);

namespace App\Application\ShadowNarrative;

use App\Domain\ShadowIdentity\ShadowIdentityPreferences;

final class ShadowSpeechDecorator
{
    /**
     * @param list<string> $lines
     *
     * @return list<string>
     */
    public function decorate(array $lines, ShadowIdentityPreferences $preferences): array
    {
        $lines[] = sprintf(
            'Speak with warmth %d/10, energy %d/10, and humor level %s.',
            $preferences->voiceProfile()->warmth(),
            $preferences->voiceProfile()->energy(),
            $preferences->humorLevel()->value,
        );

        return $lines;
    }
}
