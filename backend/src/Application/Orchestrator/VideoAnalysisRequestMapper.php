<?php

declare(strict_types=1);

namespace App\Application\Orchestrator;

use App\Domain\Orchestrator\Exception\InvalidPipelineRecommendationException;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Domain\VideoIntelligence\VideoAnalyzerInterface;
use App\Domain\VideoIntelligence\VideoIntelligence;

final class VideoAnalysisRequestMapper
{
    public function __construct(
        private readonly VideoAnalyzerInterface $analyzer,
    ) {
    }

    public function intelligenceFromArray(array $payload): VideoIntelligence
    {
        return $this->analyzer->analyze($this->inputFromArray($payload));
    }

    public function inputFromArray(array $payload): VideoAnalyzerInput
    {
        $detectedLanguage = is_string($payload['detectedLanguage'] ?? null)
            ? $payload['detectedLanguage']
            : 'english';
        $durationSeconds = is_numeric($payload['durationSeconds'] ?? null)
            ? (float) $payload['durationSeconds']
            : 120.0;
        $resolution = is_string($payload['resolution'] ?? null)
            ? $payload['resolution']
            : '1920x1080';
        $fps = is_numeric($payload['fps'] ?? null)
            ? (float) $payload['fps']
            : 30.0;
        $segmentCount = is_numeric($payload['segmentCount'] ?? null)
            ? (int) $payload['segmentCount']
            : 0;
        $transcriptText = is_string($payload['transcriptText'] ?? null)
            ? $payload['transcriptText']
            : '';
        $gpuAvailable = filter_var($payload['gpuAvailable'] ?? true, FILTER_VALIDATE_BOOL);
        $estimatedVramGb = is_numeric($payload['estimatedVramGb'] ?? null)
            ? (float) $payload['estimatedVramGb']
            : 8.0;
        $hasSlidesHint = filter_var($payload['hasSlidesHint'] ?? false, FILTER_VALIDATE_BOOL);

        return VideoAnalyzerInput::create(
            $detectedLanguage,
            $durationSeconds,
            $resolution,
            $fps,
            $segmentCount,
            $transcriptText,
            $gpuAvailable,
            $estimatedVramGb,
            $hasSlidesHint,
        );
    }

    public function defaultIntelligence(): VideoIntelligence
    {
        return $this->analyzer->analyze($this->inputFromArray([]));
    }

    public function parseStrategy(mixed $value): ?ProcessingStrategy
    {
        if (!is_string($value) || '' === trim($value)) {
            return null;
        }

        $strategy = ProcessingStrategy::tryFrom($value);

        if (null === $strategy) {
            throw new InvalidPipelineRecommendationException(sprintf('Unknown processing strategy "%s".', $value));
        }

        return $strategy;
    }
}
