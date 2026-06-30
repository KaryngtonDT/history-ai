<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\TTS;

use App\Infrastructure\TTS\FixedF5ProcessRunner;
use PHPUnit\Framework\TestCase;

final class FixedF5ProcessRunnerTest extends TestCase
{
    public function testRunCreatesOutputFileAndReturnsJson(): void
    {
        $outputPath = sys_get_temp_dir().'/history-ai-f5-test-'.uniqid('', true).'.wav';
        $runner = new FixedF5ProcessRunner();

        $output = $runner->run([
            'f5-tts',
            '--text',
            'Bonjour le monde',
            '--voice',
            'female_01',
            '--output',
            $outputPath,
        ]);

        /** @var array<string, mixed> $payload */
        $payload = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('duration', $payload);
        self::assertSame('wav', $payload['format']);
        self::assertFileExists($outputPath);

        unlink($outputPath);
    }
}
