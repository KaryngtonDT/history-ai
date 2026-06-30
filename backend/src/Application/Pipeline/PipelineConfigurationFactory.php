<?php

declare(strict_types=1);

namespace App\Application\Pipeline;

use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIEngineConfiguration;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineDefaultProviders;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;

final class PipelineConfigurationFactory
{
    public function __construct(
        private readonly AIEngineConfiguration $configuration,
    ) {
    }

    public function createDefault(): PipelineConfiguration
    {
        return PipelineConfiguration::create(
            PipelineConfigurationId::generate(),
            [
                PipelineStage::create(
                    PipelineStageType::SpeechToText,
                    $this->configuration->defaultProviderFor(AIEngineCapability::SpeechToText)
                        ?? PipelineDefaultProviders::SPEECH_TO_TEXT,
                ),
                PipelineStage::create(
                    PipelineStageType::Translation,
                    $this->configuration->defaultProviderFor(AIEngineCapability::Translation)
                        ?? PipelineDefaultProviders::TRANSLATION,
                ),
                PipelineStage::create(
                    PipelineStageType::TextToSpeech,
                    $this->configuration->defaultProviderFor(AIEngineCapability::TextToSpeech)
                        ?? PipelineDefaultProviders::TEXT_TO_SPEECH,
                ),
                PipelineStage::create(
                    PipelineStageType::VoiceClone,
                    $this->configuration->defaultProviderFor(AIEngineCapability::VoiceClone)
                        ?? PipelineDefaultProviders::VOICE_CLONE,
                ),
                PipelineStage::create(
                    PipelineStageType::LipSync,
                    $this->configuration->defaultProviderFor(AIEngineCapability::LipSync)
                        ?? PipelineDefaultProviders::LIP_SYNC,
                ),
                PipelineStage::create(
                    PipelineStageType::VideoRender,
                    $this->configuration->defaultProviderFor(AIEngineCapability::VideoRender)
                        ?? PipelineDefaultProviders::VIDEO_RENDER,
                ),
            ],
        );
    }
}
