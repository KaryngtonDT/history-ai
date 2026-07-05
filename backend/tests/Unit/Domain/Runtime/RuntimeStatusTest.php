<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Runtime;

use App\Domain\Runtime\RuntimeCapability;
use App\Domain\Runtime\RuntimeEngine;
use App\Domain\Runtime\RuntimeStatus;
use PHPUnit\Framework\TestCase;

final class RuntimeStatusTest extends TestCase
{
    public function testOperationalStatuses(): void
    {
        self::assertTrue(RuntimeStatus::Ready->isOperational());
        self::assertTrue(RuntimeStatus::Degraded->isOperational());
        self::assertFalse(RuntimeStatus::Unavailable->isOperational());
    }

    public function testRuntimeEngineSerialization(): void
    {
        $engine = new RuntimeEngine(
            id: 'faster_whisper',
            displayName: 'Faster Whisper',
            capability: RuntimeCapability::SpeechToText,
            status: RuntimeStatus::Ready,
            configured: true,
            discovered: true,
            version: 'detected',
            binaryPath: '/usr/local/bin/faster-whisper',
        );

        self::assertTrue($engine->isReady());
        self::assertSame('faster_whisper', $engine->toArray()['id']);
        self::assertSame('speech_to_text', $engine->toArray()['capability']);
    }
}
