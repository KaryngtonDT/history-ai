<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentMetadata;
use PHPUnit\Framework\TestCase;

final class AgentMetadataTest extends TestCase
{
    public function testExposesMetadataValues(): void
    {
        $metadata = new AgentMetadata([
            'resultCount' => 3,
            'topScore' => 0.91,
        ]);

        self::assertSame(
            ['resultCount' => 3, 'topScore' => 0.91],
            $metadata->values(),
        );
    }

    public function testEmptyFactoryReturnsEmptyValues(): void
    {
        self::assertSame([], AgentMetadata::empty()->values());
    }

    public function testIsImmutable(): void
    {
        $metadata = new AgentMetadata(['messageCount' => 2]);

        self::assertSame(['messageCount' => 2], $metadata->values());
    }

    public function testEqualsComparesValues(): void
    {
        $first = new AgentMetadata(['nodeCount' => 12, 'edgeCount' => 18]);
        $second = new AgentMetadata(['nodeCount' => 12, 'edgeCount' => 18]);
        $different = new AgentMetadata(['nodeCount' => 10, 'edgeCount' => 18]);

        self::assertTrue($first->equals($second));
        self::assertFalse($first->equals($different));
    }
}
