<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Pipeline\Orchestration;

use App\Application\Pipeline\Orchestration\PipelineStageCheckpointRegistry;
use App\Domain\Pipeline\PipelineStageType;
use PHPUnit\Framework\TestCase;

final class PipelineStageCheckpointRegistryTest extends TestCase
{
    public function testTranslationProcessingCheckpointInterpolatesRange(): void
    {
        $checkpoint = PipelineStageCheckpointRegistry::resolve(
            PipelineStageType::Translation,
            'translating',
        );

        self::assertSame('processing', $checkpoint['checkpoint']);
        self::assertSame(15, $checkpoint['minPercent']);
        self::assertSame(90, $checkpoint['maxPercent']);
    }

    public function testSpeechToTextTranscribingUsesProcessingCheckpoint(): void
    {
        $checkpoint = PipelineStageCheckpointRegistry::resolve(
            PipelineStageType::SpeechToText,
            'transcribing',
        );

        self::assertSame('processing', $checkpoint['checkpoint']);
        self::assertTrue(PipelineStageCheckpointRegistry::isProcessingCheckpoint($checkpoint['checkpoint']));
    }
}
