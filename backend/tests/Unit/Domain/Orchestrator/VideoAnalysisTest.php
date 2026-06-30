<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Orchestrator;

use App\Domain\Orchestrator\Exception\InvalidPipelineRecommendationException;
use App\Domain\Orchestrator\VideoAnalysis;
use PHPUnit\Framework\TestCase;

final class VideoAnalysisTest extends TestCase
{
    public function testCreateStoresAnalysisFields(): void
    {
        $analysis = VideoAnalysis::create(
            'english',
            240.0,
            '1920x1080',
            30.0,
            true,
            8.0,
        );

        self::assertSame('english', $analysis->detectedLanguage());
        self::assertSame(240.0, $analysis->durationSeconds());
        self::assertSame('1920x1080', $analysis->resolution());
        self::assertSame(30.0, $analysis->fps());
        self::assertTrue($analysis->gpuAvailable());
        self::assertSame(8.0, $analysis->estimatedVramGb());
    }

    public function testInvalidDurationThrows(): void
    {
        $this->expectException(InvalidPipelineRecommendationException::class);

        VideoAnalysis::create('english', -1.0, '1280x720', 24.0, false, 4.0);
    }
}
