<?php

declare(strict_types=1);

namespace App\Application\Pipeline;

use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Pipeline\Exception\InvalidPipelineConfigurationException;
use App\Domain\Pipeline\PipelineStageType;

final class PipelineConfigurationValidator
{
    public function __construct(
        private readonly AIProviderResolverInterface $aiProviderResolver,
    ) {
    }

    public function assertProviderEnabled(PipelineStageType $stage, string $providerId): void
    {
        $capability = $this->mapStageToCapability($stage);
        $enabledProviders = $this->aiProviderResolver->registry()->enabledProviders($capability);

        foreach ($enabledProviders as $provider) {
            if ($provider->providerId() === $providerId) {
                return;
            }
        }

        throw new InvalidPipelineConfigurationException(sprintf(
            'Provider "%s" is not enabled for stage "%s".',
            $providerId,
            $stage->value,
        ));
    }

    private function mapStageToCapability(PipelineStageType $stage): AIEngineCapability
    {
        return match ($stage) {
            PipelineStageType::SpeechToText => AIEngineCapability::SpeechToText,
            PipelineStageType::Translation => AIEngineCapability::Translation,
            PipelineStageType::TextToSpeech => AIEngineCapability::TextToSpeech,
            PipelineStageType::VoiceClone => AIEngineCapability::VoiceClone,
            PipelineStageType::LipSync => AIEngineCapability::LipSync,
            PipelineStageType::VideoRender => AIEngineCapability::VideoRender,
        };
    }
}
