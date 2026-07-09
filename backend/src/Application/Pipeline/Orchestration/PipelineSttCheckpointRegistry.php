<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

final class PipelineSttCheckpointRegistry
{
    /**
     * @return array{checkpoint: string, label: string, minPercent: int, maxPercent: int}
     */
    public static function resolve(?string $currentStep): array
    {
        $normalized = strtolower(trim((string) $currentStep));

        return match (true) {
            str_contains($normalized, 'prepar') => self::entry('preparing', 'Preparing', 0, 2),
            str_contains($normalized, 'extract') => self::entry('extracting_audio', 'Extract Audio', 2, 10),
            str_contains($normalized, 'load') && str_contains($normalized, 'model') => self::entry('loading_model', 'Loading Model', 10, 15),
            str_contains($normalized, 'transcrib') => self::entry('transcribing', 'Transcribing', 15, 95),
            str_contains($normalized, 'sav') => self::entry('saving_transcript', 'Saving Transcript', 95, 98),
            str_contains($normalized, 'complet') => self::entry('completed', 'Completed', 100, 100),
            default => self::entry('running', ucfirst(str_replace('_', ' ', $normalized ?: 'running')), 5, 90),
        };
    }

    /**
     * @return array{checkpoint: string, label: string, minPercent: int, maxPercent: int}
     */
    private static function entry(string $checkpoint, string $label, int $min, int $max): array
    {
        return [
            'checkpoint' => $checkpoint,
            'label' => $label,
            'minPercent' => $min,
            'maxPercent' => $max,
        ];
    }
}
