<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\RuntimeDashboard;

use App\Application\RuntimeDashboard\RuntimeScoreCalculator;
use PHPUnit\Framework\TestCase;

final class RuntimeScoreCalculatorTest extends TestCase
{
    public function testCalculatesWeightedRuntimeScore(): void
    {
        $calculator = new RuntimeScoreCalculator();

        $score = $calculator->calculate([
            'runtimeHealth' => 100.0,
            'compatibleInstalled' => 100.0,
            'engineTests' => 100.0,
            'benchmarks' => 100.0,
            'documentation' => 100.0,
            'hardwareCompatibility' => 100.0,
            'provisioning' => 100.0,
        ]);

        self::assertSame(100.0, $score->score);
        self::assertSame('Excellent', $score->grade);
        self::assertCount(7, $score->breakdown);
    }

    public function testClampsInputsAndProducesGrade(): void
    {
        $calculator = new RuntimeScoreCalculator();

        $score = $calculator->calculate([
            'runtimeHealth' => 120.0,
            'compatibleInstalled' => 50.0,
            'engineTests' => 50.0,
            'benchmarks' => 50.0,
            'documentation' => 50.0,
            'hardwareCompatibility' => 50.0,
            'provisioning' => 50.0,
        ]);

        self::assertGreaterThanOrEqual(50.0, $score->score);
        self::assertLessThanOrEqual(100.0, $score->score);
        self::assertNotSame('', $score->summary);
    }
}
