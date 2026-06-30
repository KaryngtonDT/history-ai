<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Optimization;

use App\Domain\Optimization\Exception\InvalidExecutionOptimizationException;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use PHPUnit\Framework\TestCase;

final class OptimizationParameterTest extends TestCase
{
    public function testCreateStoresKeyValue(): void
    {
        $parameter = OptimizationParameter::create('temperature', '0.2');

        self::assertSame('temperature', $parameter->key());
        self::assertSame('0.2', $parameter->value());
    }

    public function testCollectionLookup(): void
    {
        $collection = new OptimizationParameterCollection([
            OptimizationParameter::create('beamSize', '5'),
            OptimizationParameter::create('chunkSize', '30'),
        ]);

        self::assertSame('5', $collection->valueFor('beamSize'));
        self::assertSame(2, $collection->count());
    }

    public function testDuplicateParameterKeyThrows(): void
    {
        $this->expectException(InvalidExecutionOptimizationException::class);

        new OptimizationParameterCollection([
            OptimizationParameter::create('beamSize', '5'),
            OptimizationParameter::create('beamSize', '7'),
        ]);
    }
}
