<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Pipeline\Handlers;

use App\Application\Pipeline\Handlers\LoadPipelineConfigurationHandler;
use App\Application\Pipeline\PipelineConfigurationFactory;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineConfigurationRepositoryInterface;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use App\Infrastructure\AI\AIEngineRegistryFactory;
use PHPUnit\Framework\TestCase;

final class LoadPipelineConfigurationHandlerTest extends TestCase
{
    public function testReturnsLatestConfiguration(): void
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

        $repository = $this->createStub(PipelineConfigurationRepositoryInterface::class);
        $repository->method('findLatest')->willReturn($configuration);

        $handler = new LoadPipelineConfigurationHandler(
            $repository,
            new PipelineConfigurationFactory((new AIEngineRegistryFactory())->createConfiguration()),
        );

        $result = $handler();

        self::assertSame('ollama', $result->stages[1]['providerId']);
    }

    public function testFallsBackToDefaultsWhenMissing(): void
    {
        $repository = $this->createStub(PipelineConfigurationRepositoryInterface::class);
        $repository->method('findLatest')->willReturn(null);

        $handler = new LoadPipelineConfigurationHandler(
            $repository,
            new PipelineConfigurationFactory((new AIEngineRegistryFactory())->createConfiguration()),
        );

        $result = $handler();

        self::assertCount(6, $result->stages);
        self::assertSame('faster_whisper', $result->stages[0]['providerId']);
    }
}
