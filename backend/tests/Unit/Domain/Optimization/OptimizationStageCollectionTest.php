<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Optimization;

use App\Domain\Optimization\Exception\InvalidExecutionOptimizationException;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
use PHPUnit\Framework\TestCase;

final class OptimizationStageCollectionTest extends TestCase
{
    public function testForStageReturnsConfiguration(): void
    {
        $collection = new OptimizationStageCollection([
            OptimizationStageConfiguration::create(
                OptimizationStage::Translation,
                OptimizationParameterCollection::empty(),
            ),
        ]);

        $configuration = $collection->forStage(OptimizationStage::Translation);

        self::assertNotNull($configuration);
        self::assertSame(OptimizationStage::Translation, $configuration->stage());
    }

    public function testDuplicateStageThrows(): void
    {
        $this->expectException(InvalidExecutionOptimizationException::class);

        new OptimizationStageCollection([
            OptimizationStageConfiguration::create(
                OptimizationStage::SpeechToText,
                OptimizationParameterCollection::empty(),
            ),
            OptimizationStageConfiguration::create(
                OptimizationStage::SpeechToText,
                OptimizationParameterCollection::empty(),
            ),
        ]);
    }
}
