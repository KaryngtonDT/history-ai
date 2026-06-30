<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Orchestrator;

use App\Domain\Orchestrator\ProcessingStrategy;
use PHPUnit\Framework\TestCase;

final class ProcessingStrategyTest extends TestCase
{
    public function testStrategyValues(): void
    {
        self::assertSame('balanced', ProcessingStrategy::Balanced->value);
        self::assertSame('quality', ProcessingStrategy::Quality->value);
        self::assertSame('speed', ProcessingStrategy::Speed->value);
        self::assertSame('low_memory', ProcessingStrategy::LowMemory->value);
    }
}
