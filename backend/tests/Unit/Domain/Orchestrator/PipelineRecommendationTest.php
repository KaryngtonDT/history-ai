<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Orchestrator;

use App\Domain\Orchestrator\Exception\InvalidPipelineRecommendationException;
use App\Domain\Orchestrator\PipelineRecommendation;
use App\Domain\Orchestrator\PipelineRecommendationId;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use PHPUnit\Framework\TestCase;

final class PipelineRecommendationTest extends TestCase
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

    public function testCreateRecommendation(): void
    {
        $configuration = PipelineConfiguration::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            $this->completeStages(),
        );

        $recommendation = PipelineRecommendation::create(
            PipelineRecommendationId::generate(),
            ProcessingStrategy::Balanced,
            $configuration,
            'Balanced pipeline for English content with GPU available.',
            240,
            5,
            8.0,
        );

        self::assertSame(ProcessingStrategy::Balanced, $recommendation->strategy());
        self::assertSame('ollama', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::Translation));
        self::assertSame(240, $recommendation->estimatedDurationSeconds());
        self::assertSame(5, $recommendation->estimatedQuality());
    }

    public function testInvalidQualityThrows(): void
    {
        $configuration = PipelineConfiguration::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            $this->completeStages(),
        );

        $this->expectException(InvalidPipelineRecommendationException::class);

        PipelineRecommendation::create(
            PipelineRecommendationId::generate(),
            ProcessingStrategy::Speed,
            $configuration,
            'Speed-optimized pipeline.',
            120,
            6,
            4.0,
        );
    }
}
