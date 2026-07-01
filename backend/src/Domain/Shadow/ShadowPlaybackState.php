<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

enum ShadowPlaybackState: string
{
    case Playing = 'playing';
    case Paused = 'paused';
    case Ended = 'ended';

    public function canPause(): bool
    {
        return self::Playing === $this;
    }

    public function canResume(): bool
    {
        return self::Paused === $this;
    }
}
