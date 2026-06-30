<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Pipeline;

use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineConfigurationRepositoryInterface;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use PHPUnit\Framework\TestCase;

final class PipelineConfigurationRepositoryInterfaceTest extends TestCase
{
    public function testRepositoryInterfaceDefinesExpectedMethods(): void
    {
        $configuration = PipelineConfiguration::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            [
                PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
                PipelineStage::create(PipelineStageType::Translation, 'ollama'),
                PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
                PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
                PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
                PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
            ],
        );

        $repository = $this->createMock(PipelineConfigurationRepositoryInterface::class);

        $repository
            ->expects(self::once())
            ->method('save')
            ->with($configuration);

        $repository
            ->expects(self::once())
            ->method('findLatest')
            ->willReturn($configuration);

        $repository
            ->expects(self::once())
            ->method('deleteAll');

        $repository->save($configuration);
        self::assertSame($configuration, $repository->findLatest());
        $repository->deleteAll();
    }
}
