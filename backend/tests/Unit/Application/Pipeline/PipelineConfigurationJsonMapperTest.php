<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Pipeline;

use App\Application\Pipeline\PipelineConfigurationJsonMapper;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use PHPUnit\Framework\TestCase;

final class PipelineConfigurationJsonMapperTest extends TestCase
{
    private PipelineConfigurationJsonMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new PipelineConfigurationJsonMapper();
    }

    public function testRoundTripsConfiguration(): void
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
            2,
            new \DateTimeImmutable('2026-06-26T10:00:00+00:00'),
            new \DateTimeImmutable('2026-06-26T11:00:00+00:00'),
        );

        $decoded = $this->mapper->fromJson($this->mapper->toJson($configuration));

        self::assertTrue($configuration->id()->equals($decoded->id()));
        self::assertSame(2, $decoded->version());
        self::assertSame('ollama', $decoded->providerFor(PipelineStageType::Translation));
    }
}
