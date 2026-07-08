<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Pipeline\Estimation;

use App\Application\Pipeline\Estimation\HardwareAwareEstimateResolver;
use App\Application\Pipeline\Estimation\MediaDurationResolver;
use App\Application\Pipeline\Estimation\PipelineStageDurationEstimator;
use App\Application\Pipeline\Estimation\TranscriptionDurationEstimator;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class PipelineStageDurationEstimatorTest extends TestCase
{
    public function testTranslationFallbackEstimateWhenMediaDurationUnknown(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $videoRepository = $this->createStub(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(null);

        $estimator = $this->createEstimator($videoRepository);
        $estimate = $estimator->estimateForStage($videoId, PipelineStageType::Translation);

        self::assertNull($estimate['mediaDurationSeconds']);
        self::assertSame(600, $estimate['maxSeconds']);
        self::assertGreaterThanOrEqual(60, $estimate['minSeconds']);
    }

    public function testSpeechToTextDelegatesToTranscriptionEstimator(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $videoRepository = $this->createStub(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(null);

        $estimator = $this->createEstimator($videoRepository);
        $estimate = $estimator->estimateForStage($videoId, PipelineStageType::SpeechToText);

        self::assertNotNull($estimate['maxSeconds']);
        self::assertGreaterThan(0, $estimate['maxSeconds']);
    }

    public function testTextToSpeechFallbackEstimateWhenMediaDurationUnknown(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $videoRepository = $this->createStub(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(null);

        $estimator = $this->createEstimator($videoRepository);
        $estimate = $estimator->estimateForStage($videoId, PipelineStageType::TextToSpeech);

        self::assertSame(900, $estimate['maxSeconds']);
    }

    private function createEstimator(VideoRepositoryInterface $videoRepository): PipelineStageDurationEstimator
    {
        return new PipelineStageDurationEstimator(
            new TranscriptionDurationEstimator(
                new MediaDurationResolver($videoRepository),
                new HardwareAwareEstimateResolver(false),
                'large-v3',
            ),
            new MediaDurationResolver($videoRepository),
        );
    }
}
