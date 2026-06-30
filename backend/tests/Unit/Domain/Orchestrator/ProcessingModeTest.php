<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Orchestrator;

use App\Domain\Orchestrator\ProcessingMode;
use PHPUnit\Framework\TestCase;

final class ProcessingModeTest extends TestCase
{
    public function testManualAndAutomaticValues(): void
    {
        self::assertSame('manual', ProcessingMode::Manual->value);
        self::assertSame('automatic', ProcessingMode::Automatic->value);
    }
}
