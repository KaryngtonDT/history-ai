<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\LipSync;

use App\Infrastructure\LipSync\FixedLatentSyncProcessRunner;
use PHPUnit\Framework\TestCase;

final class FixedLatentSyncProcessRunnerTest extends TestCase
{
    public function testRunCreatesOutputFileAndReturnsJson(): void
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-latentsync-runner-'.uniqid('', true);
        mkdir($outputDirectory);
        $outputPath = $outputDirectory.'/synced.mp4';

        $runner = new FixedLatentSyncProcessRunner();
        $result = $runner->run([
            'latentsync',
            '--audio-duration',
            '8.0',
            '--output',
            $outputPath,
        ]);

        /** @var array<string, mixed> $payload */
        $payload = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals(8.0, $payload['duration']);
        self::assertFileExists($outputPath);

        unlink($outputPath);
        rmdir($outputDirectory);
    }
}
