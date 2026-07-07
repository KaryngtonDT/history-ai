<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Pipeline;

use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineConfigurationRepositoryInterface;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use App\Infrastructure\Pipeline\ResolvingPipelineConfiguration;
use App\Infrastructure\Pipeline\RuntimePipelineConfigurationContext;
use PHPUnit\Framework\TestCase;

final class ResolvingPipelineConfigurationTest extends TestCase
{
    public function testPrefersRuntimeOverrideOverRepository(): void
    {
        $runtime = new RuntimePipelineConfigurationContext();
        $repository = $this->createStub(PipelineConfigurationRepositoryInterface::class);

        $runtimeConfiguration = PipelineConfiguration::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440011'),
            [
                PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
                PipelineStage::create(PipelineStageType::Translation, 'ollama'),
                PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
                PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
                PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
                PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
            ],
        );

        $savedConfiguration = PipelineConfiguration::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440012'),
            [
                PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
                PipelineStage::create(PipelineStageType::Translation, 'mock'),
                PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
                PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
                PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
                PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
            ],
        );

        $runtime->set($runtimeConfiguration);
        $repository->method('findLatest')->willReturn($savedConfiguration);

        $resolver = new ResolvingPipelineConfiguration($runtime, $repository);

        self::assertSame('ollama', $resolver->resolve()?->providerFor(PipelineStageType::Translation));
    }

    public function testFallsBackToRepositoryWhenRuntimeEmpty(): void
    {
        $runtime = new RuntimePipelineConfigurationContext();
        $repository = $this->createStub(PipelineConfigurationRepositoryInterface::class);
        $repository->method('findLatest')->willReturn(null);

        $resolver = new ResolvingPipelineConfiguration($runtime, $repository);

        self::assertNull($resolver->resolve());
    }
}
