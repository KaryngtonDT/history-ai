<?php

declare(strict_types=1);

namespace App\Application\Runtime;

use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Pipeline\PipelineStageType;

final class PipelineStageCapabilityMapper
{
    public static function fromPipelineStage(PipelineStageType $stage): EngineCatalogCapability
    {
        return match ($stage) {
            PipelineStageType::SpeechToText => EngineCatalogCapability::SpeechToText,
            PipelineStageType::Translation => EngineCatalogCapability::Translation,
            PipelineStageType::TextToSpeech => EngineCatalogCapability::TextToSpeech,
            PipelineStageType::VoiceClone => EngineCatalogCapability::VoiceClone,
            PipelineStageType::LipSync => EngineCatalogCapability::LipSync,
            PipelineStageType::VideoRender => EngineCatalogCapability::VideoRender,
        };
    }

    public static function toPipelineStage(EngineCatalogCapability $capability): ?PipelineStageType
    {
        return match ($capability) {
            EngineCatalogCapability::SpeechToText => PipelineStageType::SpeechToText,
            EngineCatalogCapability::Translation => PipelineStageType::Translation,
            EngineCatalogCapability::TextToSpeech => PipelineStageType::TextToSpeech,
            EngineCatalogCapability::VoiceClone => PipelineStageType::VoiceClone,
            EngineCatalogCapability::LipSync => PipelineStageType::LipSync,
            EngineCatalogCapability::VideoRender => PipelineStageType::VideoRender,
            default => null,
        };
    }

    public static function hardwarePipelineKey(EngineCatalogCapability $capability): ?string
    {
        return match ($capability) {
            EngineCatalogCapability::SpeechToText => 'speech',
            EngineCatalogCapability::Translation => 'translation',
            EngineCatalogCapability::TextToSpeech => 'tts',
            EngineCatalogCapability::VoiceClone => 'voiceClone',
            EngineCatalogCapability::LipSync => 'lipSync',
            EngineCatalogCapability::VideoRender => 'render',
            default => null,
        };
    }
}
