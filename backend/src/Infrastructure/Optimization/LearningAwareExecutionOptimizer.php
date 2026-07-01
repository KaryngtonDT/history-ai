<?php

declare(strict_types=1);

namespace App\Infrastructure\Optimization;

use App\Application\Learning\LearningAdaptiveAdvisor;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
use App\Domain\VideoIntelligence\VideoIntelligence;

final class LearningAwareExecutionOptimizer implements ExecutionOptimizerInterface
{
    public function __construct(
        private readonly DeterministicExecutionOptimizer $inner,
        private readonly LearningAdaptiveAdvisor $advisor,
    ) {
    }

    public function optimize(VideoIntelligence $intelligence): ExecutionOptimization
    {
        $optimization = $this->inner->optimize($intelligence);
        $hints = $this->advisor->hints();

        if (!$hints->active || null === $hints->translationStyle) {
            return $optimization;
        }

        $translationStage = $optimization->stages()->forStage(OptimizationStage::Translation);

        if (null === $translationStage) {
            return $optimization;
        }

        $parameters = [];

        foreach ($translationStage->parameters()->all() as $parameter) {
            if ('style' === $parameter->key()) {
                continue;
            }

            $parameters[] = $parameter;
        }

        $parameters[] = OptimizationParameter::create('style', $hints->translationStyle);

        $stages = [];

        foreach ($optimization->stages()->all() as $stageConfiguration) {
            if (OptimizationStage::Translation === $stageConfiguration->stage()) {
                $stages[] = OptimizationStageConfiguration::create(
                    OptimizationStage::Translation,
                    new OptimizationParameterCollection($parameters),
                );
                continue;
            }

            $stages[] = $stageConfiguration;
        }

        $explanations = $optimization->explanations();
        $explanations[] = sprintf(
            'Adaptive learning applied %s translation style preference.',
            $hints->translationStyle,
        );

        return ExecutionOptimization::create(
            $optimization->id(),
            $optimization->profile(),
            new OptimizationStageCollection($stages),
            $optimization->summary(),
            $optimization->estimatedImpact(),
            $explanations,
        );
    }
}
