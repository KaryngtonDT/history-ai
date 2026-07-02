<?php

declare(strict_types=1);

namespace App\Application\ShadowNarrative;

final class ShadowStorytellingDecorator
{
    /**
     * @param list<string> $lines
     *
     * @return list<string>
     */
    public function decorate(array $lines): array
    {
        $lines[] = 'Tell the answer as a short story with a clear beginning, turning point, and takeaway.';

        return $lines;
    }
}
