<?php

declare(strict_types=1);

namespace App\Infrastructure\Orchestrator;

use App\Application\Learning\LearningAdaptiveAdvisor;
use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIEngineRegistry;
use App\Domain\Orchestrator\PipelinePlannerInterface;
use App\Domain\Orchestrator\PipelineRecommendation;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Review\UserPreferenceProfile;
use App\Domain\VideoIntelligence\VideoIntelligence;

final class LearningAwarePipelinePlanner implements PipelinePlannerInterface
{
    public function __construct(
        private readonly PipelinePlannerInterface $inner,
        private readonly LearningAdaptiveAdvisor $advisor,
        private readonly AIEngineRegistry $registry,
    ) {
    }

    public function recommend(
        VideoIntelligence $intelligence,
        ?UserPreferenceProfile $preferences = null,
    ): PipelineRecommendation {
        return $this->applyLearningHints(
            $this->inner->recommend($intelligence, $preferences),
        );
    }

    public function recommendWithStrategy(
        VideoIntelligence $intelligence,
        ProcessingStrategy $strategy,
        ?UserPreferenceProfile $preferences = null,
    ): PipelineRecommendation {
        return $this->applyLearningHints(
            $this->inner->recommendWithStrategy($intelligence, $strategy, $preferences),
        );
    }

    private function applyLearningHints(PipelineRecommendation $recommendation): PipelineRecommendation
    {
        $hints = $this->advisor->hints();

        if (!$hints->active || null === $hints->preferredProvider) {
            return $recommendation;
        }

        $providerId = $hints->preferredProvider;
        $enabledIds = array_map(
            static fn ($provider) => $provider->providerId(),
            $this->registry->enabledProviders(AIEngineCapability::Translation),
        );

        if (!in_array($providerId, $enabledIds, true)) {
            $reasons = $recommendation->reasons();
            $reasons[] = sprintf(
                'Adaptive learning suggests provider "%s", but it is not currently enabled.',
                $providerId,
            );

            return PipelineRecommendation::create(
                $recommendation->id(),
                $recommendation->strategy(),
                $recommendation->pipelineConfiguration(),
                $recommendation->explanation().' Adaptive learning noted a soft provider preference.',
                $recommendation->estimatedDurationSeconds(),
                $recommendation->estimatedQuality(),
                $recommendation->estimatedVramGb(),
                $reasons,
            );
        }

        $stages = [];

        foreach ($recommendation->pipelineConfiguration()->stages()->all() as $stage) {
            if (PipelineStageType::Translation === $stage->stage()) {
                $stages[] = PipelineStage::create(PipelineStageType::Translation, $providerId);
                continue;
            }

            $stages[] = $stage;
        }

        $configuration = PipelineConfiguration::create(
            $recommendation->pipelineConfiguration()->id(),
            $stages,
        );

        $reasons = $recommendation->reasons();
        $reasons[] = sprintf(
            'Adaptive learning applied soft provider preference "%s" for translation.',
            $providerId,
        );

        return PipelineRecommendation::create(
            $recommendation->id(),
            $recommendation->strategy(),
            $configuration,
            $recommendation->explanation().' Translation provider adjusted from adaptive learning.',
            $recommendation->estimatedDurationSeconds(),
            $recommendation->estimatedQuality(),
            $recommendation->estimatedVramGb(),
            $reasons,
        );
    }
}
