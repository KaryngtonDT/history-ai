<?php

declare(strict_types=1);

namespace App\Application\Orchestrator;

use App\Domain\Orchestrator\Exception\InvalidPipelineRecommendationException;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Orchestrator\VideoAnalysis;

final class VideoAnalysisRequestMapper
{
    public function fromArray(array $payload): VideoAnalysis
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
        $gpuAvailable = filter_var($payload['gpuAvailable'] ?? true, FILTER_VALIDATE_BOOL);
        $estimatedVramGb = is_numeric($payload['estimatedVramGb'] ?? null)
            ? (float) $payload['estimatedVramGb']
            : 8.0;

        return VideoAnalysis::create(
            $detectedLanguage,
            $durationSeconds,
            $resolution,
            $fps,
            $gpuAvailable,
            $estimatedVramGb,
        );
    }

    public function defaultAnalysis(): VideoAnalysis
    {
        return VideoAnalysis::create('english', 120.0, '1920x1080', 30.0, true, 8.0);
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
