<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Optimization;

use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
use App\Infrastructure\Optimization\RuntimeExecutionOptimizationContext;
use PHPUnit\Framework\TestCase;

final class RuntimeExecutionOptimizationContextTest extends TestCase
{
    public function testStoresAndClearsOptimization(): void
    {
        $context = new RuntimeExecutionOptimizationContext();
        $optimization = ExecutionOptimization::create(
            ExecutionOptimizationId::generate(),
            OptimizationProfile::Balanced,
            new OptimizationStageCollection([
                OptimizationStageConfiguration::create(
                    OptimizationStage::Translation,
                    new OptimizationParameterCollection([
                        OptimizationParameter::create('temperature', '0.2'),
                    ]),
                ),
            ]),
            'Balanced optimization.',
            4,
        );

        $context->set($optimization);

        self::assertSame($optimization, $context->get());

        $context->clear();

        self::assertNull($context->get());
    }
}
