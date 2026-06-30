<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VoiceClone;

use App\Infrastructure\VoiceClone\FixedOpenVoiceProcessRunner;
use PHPUnit\Framework\TestCase;

final class FixedOpenVoiceProcessRunnerTest extends TestCase
{
    public function testRunCreatesOutputFileAndReturnsJson(): void
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-openvoice-runner-'.uniqid('', true);
        mkdir($outputDirectory);
        $outputPath = $outputDirectory.'/cloned.wav';

        $runner = new FixedOpenVoiceProcessRunner();
        $result = $runner->run([
            'openvoice',
            '--source-duration',
            '6.0',
            '--output',
            $outputPath,
        ]);

        /** @var array<string, mixed> $payload */
        $payload = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals(6.0, $payload['duration']);
        self::assertSame(44100, $payload['sampleRate']);
        self::assertFileExists($outputPath);

        unlink($outputPath);
        rmdir($outputDirectory);
    }
}
