<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Domain\Pipeline\PipelineStageType;

final class PipelineDependencyResolver
{
    /**
     * @return list<string>
     */
    public function invalidatesStages(PipelineStageType $stage): array
    {
        return match ($stage) {
            PipelineStageType::SpeechToText => [
                PipelineStageType::Translation->value,
                PipelineStageType::TextToSpeech->value,
                PipelineStageType::VoiceClone->value,
                PipelineStageType::LipSync->value,
                PipelineStageType::VideoRender->value,
                'quality',
            ],
            PipelineStageType::Translation => [
                PipelineStageType::TextToSpeech->value,
                PipelineStageType::VoiceClone->value,
                PipelineStageType::LipSync->value,
                PipelineStageType::VideoRender->value,
                'quality',
            ],
            PipelineStageType::TextToSpeech => [
                PipelineStageType::VoiceClone->value,
                PipelineStageType::LipSync->value,
                PipelineStageType::VideoRender->value,
                'quality',
            ],
            PipelineStageType::VoiceClone => [
                PipelineStageType::LipSync->value,
                PipelineStageType::VideoRender->value,
                'quality',
            ],
            PipelineStageType::LipSync => [
                PipelineStageType::VideoRender->value,
                'quality',
            ],
            PipelineStageType::VideoRender => ['quality'],
        };
    }

    public function nextStage(PipelineStageType $completedStage): ?PipelineStageType
    {
        return match ($completedStage) {
            PipelineStageType::SpeechToText => PipelineStageType::Translation,
            PipelineStageType::Translation => PipelineStageType::TextToSpeech,
            PipelineStageType::TextToSpeech => PipelineStageType::VoiceClone,
            PipelineStageType::VoiceClone => PipelineStageType::LipSync,
            PipelineStageType::LipSync => PipelineStageType::VideoRender,
            PipelineStageType::VideoRender => null,
        };
    }
}
