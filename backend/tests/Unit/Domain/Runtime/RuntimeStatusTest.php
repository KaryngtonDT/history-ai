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
        self::assertFalse(RuntimeStatus::Degraded->isOperational());
        self::assertFalse(RuntimeStatus::Mock->isOperational());
        self::assertFalse(RuntimeStatus::Missing->isOperational());
        self::assertFalse(RuntimeStatus::Unavailable->isOperational());
    }

    public function testRuntimeEngineSerialization(): void
    {
        $engine = new RuntimeEngine(
            id: 'faster_whisper_large_v3',
            displayName: 'Faster Whisper Large V3',
            capability: RuntimeCapability::SpeechToText,
            status: RuntimeStatus::Ready,
            mode: \App\Domain\Runtime\EngineExecutionMode::Real,
            configured: true,
            discovered: true,
            executableFound: true,
            modelFound: true,
            version: 'detected',
            binaryPath: '/usr/local/bin/faster-whisper',
        );

        self::assertTrue($engine->isReady());
        self::assertSame('faster_whisper_large_v3', $engine->toArray()['id']);
        self::assertSame('real', $engine->toArray()['mode']);
        self::assertSame('speech_to_text', $engine->toArray()['capability']);
    }
}
