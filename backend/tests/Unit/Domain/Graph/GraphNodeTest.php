<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Graph;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Graph\GraphNode;
use PHPUnit\Framework\TestCase;

final class GraphNodeTest extends TestCase
{
    public function testExposesArtifactIdentityTypeAndLabel(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440000');

        $node = new GraphNode($artifactId, ArtifactType::Summary, 'Summary');

        self::assertTrue($node->artifactId()->equals($artifactId));
        self::assertSame(ArtifactType::Summary, $node->type());
        self::assertSame('Summary', $node->label());
    }

    public function testIsImmutable(): void
    {
        $node = new GraphNode(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440000'),
            ArtifactType::Summary,
            'Summary',
        );

        self::assertSame('Summary', $node->label());
        self::assertSame(ArtifactType::Summary, $node->type());
    }
}
