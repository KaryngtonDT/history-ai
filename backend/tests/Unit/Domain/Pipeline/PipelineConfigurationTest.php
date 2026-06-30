<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Pipeline;

use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use PHPUnit\Framework\TestCase;

final class PipelineConfigurationTest extends TestCase
{
    /**
     * @return list<PipelineStage>
     */
    private function completeStages(): array
    {
        return [
            PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
            PipelineStage::create(PipelineStageType::Translation, 'ollama'),
            PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
            PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
            PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
            PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
        ];
    }

    public function testCreateValidatesCompletePipeline(): void
    {
        $configuration = PipelineConfiguration::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            $this->completeStages(),
        );

        self::assertSame(6, $configuration->stageCount());
        self::assertSame('ollama', $configuration->providerFor(PipelineStageType::Translation));
    }

    public function testReplaceReturnsUpdatedConfiguration(): void
    {
        $configuration = PipelineConfiguration::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            $this->completeStages(),
        );

        $updated = $configuration->replace(PipelineStageType::Translation, 'mock');

        self::assertSame('mock', $updated->providerFor(PipelineStageType::Translation));
        self::assertSame('ollama', $configuration->providerFor(PipelineStageType::Translation));
    }

    public function testIncompletePipelineThrows(): void
    {
        $this->expectException(InvalidPipelineConfigurationException::class);

        PipelineConfiguration::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            [
                PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
            ],
        );
    }

    public function testInvalidVersionThrows(): void
    {
        $this->expectException(InvalidPipelineConfigurationException::class);

        new PipelineConfiguration(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            \App\Domain\Pipeline\PipelineStageCollection::fromStages($this->completeStages()),
            0,
        );
    }
}
