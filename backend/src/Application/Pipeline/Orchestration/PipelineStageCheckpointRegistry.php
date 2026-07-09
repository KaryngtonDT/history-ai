<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Domain\Pipeline\PipelineStageType;

final class PipelineStageCheckpointRegistry
{
    public const HEARTBEAT_INTERVAL_SECONDS = 10;

    /**
     * @return array{checkpoint: string, label: string, minPercent: int, maxPercent: int, heartbeatIntervalSeconds: int}
     */
    public static function resolve(PipelineStageType $stage, ?string $currentStep): array
    {
        $normalized = strtolower(trim((string) $currentStep));

        return match ($stage) {
            PipelineStageType::SpeechToText => self::resolveSpeechToText($normalized),
            PipelineStageType::Translation => self::resolveGeneric($normalized, 'translation'),
            PipelineStageType::TextToSpeech => self::resolveGeneric($normalized, 'audio'),
            PipelineStageType::VoiceClone => self::resolveGeneric($normalized, 'voice_clone'),
            PipelineStageType::LipSync => self::resolveGeneric($normalized, 'lip_sync'),
            PipelineStageType::VideoRender => self::resolveGeneric($normalized, 'render'),
        };
    }

    /**
     * @return list<array{checkpoint: string, label: string, minPercent: int, maxPercent: int}>
     */
    public static function checkpointsFor(PipelineStageType $stage): array
    {
        return match ($stage) {
            PipelineStageType::SpeechToText => [
                self::entry('preparing', 'Preparing', 0, 2),
                self::entry('loading', 'Loading Model', 10, 15),
                self::entry('extracting_audio', 'Extract Audio', 2, 10),
                self::entry('processing', 'Transcribing', 15, 95),
                self::entry('saving', 'Saving Transcript', 95, 98),
                self::entry('completed', 'Completed', 100, 100),
            ],
            default => [
                self::entry('preparing', 'Preparing', 0, 5),
                self::entry('loading', 'Loading', 5, 15),
                self::entry('processing', 'Processing', 15, 90),
                self::entry('saving', 'Saving', 90, 98),
                self::entry('completed', 'Completed', 100, 100),
            ],
        };
    }

    public static function isProcessingCheckpoint(string $checkpoint): bool
    {
        return in_array($checkpoint, [
            'processing',
            'transcribing',
            'translating',
            'generating',
            'extracting_audio',
        ], true);
    }

    /**
     * @return array{checkpoint: string, label: string, minPercent: int, maxPercent: int, heartbeatIntervalSeconds: int}
     */
    private static function resolveSpeechToText(string $normalized): array
    {
        return match (true) {
            str_contains($normalized, 'prepar') => self::withHeartbeat(self::entry('preparing', 'Preparing', 0, 2)),
            str_contains($normalized, 'extract') => self::withHeartbeat(self::entry('extracting_audio', 'Extract Audio', 2, 10)),
            str_contains($normalized, 'load') => self::withHeartbeat(self::entry('loading', 'Loading Model', 10, 15)),
            str_contains($normalized, 'transcrib') => self::withHeartbeat(self::entry('processing', 'Transcribing', 15, 95)),
            str_contains($normalized, 'sav') => self::withHeartbeat(self::entry('saving', 'Saving Transcript', 95, 98)),
            str_contains($normalized, 'complet') => self::withHeartbeat(self::entry('completed', 'Completed', 100, 100)),
            default => self::withHeartbeat(self::entry('preparing', 'Preparing', 0, 5)),
        };
    }

    /**
     * @return array{checkpoint: string, label: string, minPercent: int, maxPercent: int, heartbeatIntervalSeconds: int}
     */
    private static function resolveGeneric(string $normalized, string $stageLabel): array
    {
        return match (true) {
            str_contains($normalized, 'prepar') => self::withHeartbeat(self::entry('preparing', 'Preparing', 0, 5)),
            str_contains($normalized, 'load') => self::withHeartbeat(self::entry('loading', 'Loading', 5, 15)),
            str_contains($normalized, 'sav') => self::withHeartbeat(self::entry('saving', 'Saving', 90, 98)),
            str_contains($normalized, 'complet') => self::withHeartbeat(self::entry('completed', 'Completed', 100, 100)),
            str_contains($normalized, 'translat'),
            str_contains($normalized, 'generat'),
            str_contains($normalized, 'process'),
            str_contains($normalized, 'render'),
            str_contains($normalized, 'sync'),
            str_contains($normalized, 'clone'),
            str_contains($normalized, 'started') => self::withHeartbeat(self::entry('processing', ucfirst($stageLabel), 15, 90)),
            default => self::withHeartbeat(self::entry('preparing', 'Preparing', 0, 5)),
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

    /**
     * @param array{checkpoint: string, label: string, minPercent: int, maxPercent: int} $entry
     *
     * @return array{checkpoint: string, label: string, minPercent: int, maxPercent: int, heartbeatIntervalSeconds: int}
     */
    private static function withHeartbeat(array $entry): array
    {
        return [...$entry, 'heartbeatIntervalSeconds' => self::HEARTBEAT_INTERVAL_SECONDS];
    }
}
