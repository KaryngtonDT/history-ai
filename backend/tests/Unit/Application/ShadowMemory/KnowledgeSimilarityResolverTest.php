<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowMemory;

use App\Application\ShadowMemory\KnowledgeSimilarityResolver;
use PHPUnit\Framework\TestCase;

final class KnowledgeSimilarityResolverTest extends TestCase
{
    private KnowledgeSimilarityResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new KnowledgeSimilarityResolver();
    }

    public function testExtractConceptsFindsKnownAliases(): void
    {
        $concepts = $this->resolver->extractConcepts('How does dependency injection work in Symfony Messenger?');

        self::assertNotEmpty($concepts);
        self::assertSame('dependency_injection', $concepts[0]['key']);
        self::assertTrue(
            array_any($concepts, static fn (array $concept): bool => 'symfony_messenger' === $concept['key']),
        );
    }

    public function testExtractConceptsReturnsEmptyForUnknownTopics(): void
    {
        self::assertSame([], $this->resolver->extractConcepts('What is the weather today?'));
    }

    public function testNormalizeMapsAliases(): void
    {
        self::assertSame('dependency_injection', $this->resolver->normalize('dependency injection'));
        self::assertSame('kubernetes', $this->resolver->normalize('k8s'));
    }
}
