<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\ShadowPlaybackState;
use PHPUnit\Framework\TestCase;

final class ShadowPlaybackStateTest extends TestCase
{
    public function testPauseAndResumeGuards(): void
    {
        self::assertTrue(ShadowPlaybackState::Playing->canPause());
        self::assertFalse(ShadowPlaybackState::Paused->canPause());
        self::assertTrue(ShadowPlaybackState::Paused->canResume());
        self::assertFalse(ShadowPlaybackState::Playing->canResume());
        self::assertFalse(ShadowPlaybackState::Ended->canResume());
    }
}
