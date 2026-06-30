<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VideoRender;

use App\Infrastructure\VideoRender\FixedFFmpegProcessRunner;
use PHPUnit\Framework\TestCase;

final class FixedFFmpegProcessRunnerTest extends TestCase
{
    public function testRunCreatesOutputFileAndReturnsJson(): void
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-ffmpeg-runner-'.uniqid('', true);
        mkdir($outputDirectory);
        $outputPath = $outputDirectory.'/final.mp4';

        $runner = new FixedFFmpegProcessRunner();
        $result = $runner->run([
            'ffmpeg',
            '-y',
            '-i',
            '/tmp/synced.mp4',
            '-t',
            '12.0',
            '-crf',
            '23',
            $outputPath,
        ]);

        /** @var array<string, mixed> $payload */
        $payload = json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals(12.0, $payload['duration']);
        self::assertFileExists($outputPath);

        unlink($outputPath);
        rmdir($outputDirectory);
    }
}
