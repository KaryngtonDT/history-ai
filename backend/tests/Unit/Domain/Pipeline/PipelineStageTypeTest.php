<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Pipeline;

use App\Domain\Pipeline\PipelineStageType;
use PHPUnit\Framework\TestCase;

final class PipelineStageTypeTest extends TestCase
{
    public function testAllStagesAreDefined(): void
    {
        self::assertCount(6, PipelineStageType::all());
        self::assertSame('speech_to_text', PipelineStageType::SpeechToText->value);
        self::assertSame('video_render', PipelineStageType::VideoRender->value);
    }
}
