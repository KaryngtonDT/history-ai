<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Optimization;

use App\Domain\Optimization\Exception\InvalidExecutionOptimizationException;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
use PHPUnit\Framework\TestCase;

final class ExecutionOptimizationTest extends TestCase
{
    public function testCreateStoresOptimizationFields(): void
    {
        $optimization = $this->createOptimization();

        self::assertTrue(ExecutionOptimizationId::isValid($optimization->id()->value));
        self::assertSame(OptimizationProfile::Quality, $optimization->profile());
        self::assertSame(6, $optimization->stages()->count());
        self::assertSame(5, $optimization->estimatedImpact());
        self::assertNotEmpty($optimization->explanations());
    }

    public function testInvalidImpactThrows(): void
    {
        $this->expectException(InvalidExecutionOptimizationException::class);

        ExecutionOptimization::create(
            ExecutionOptimizationId::generate(),
            OptimizationProfile::Balanced,
            $this->stageCollection(),
            'Summary',
            6,
        );
    }

    private function createOptimization(): ExecutionOptimization
    {
        return ExecutionOptimization::create(
            ExecutionOptimizationId::generate(),
            OptimizationProfile::Quality,
            $this->stageCollection(),
            'Quality-first execution optimization.',
            5,
            ['Low STT confidence: beam size increased to 5.'],
        );
    }

    private function stageCollection(): OptimizationStageCollection
    {
        $stages = [];

        foreach (OptimizationStage::all() as $stage) {
            $stages[] = OptimizationStageConfiguration::create(
                $stage,
                new OptimizationParameterCollection([
                    OptimizationParameter::create('beamSize', '5'),
                ]),
            );
        }

        return new OptimizationStageCollection($stages);
    }
}
