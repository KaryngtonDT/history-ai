<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Semantic;

use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingProviderInterface;
use App\Infrastructure\Semantic\DeterministicEmbeddingProvider;
use PHPUnit\Framework\TestCase;

final class DeterministicEmbeddingProviderTest extends TestCase
{
    private DeterministicEmbeddingProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new DeterministicEmbeddingProvider();
    }

    public function testImplementsEmbeddingProviderInterface(): void
    {
        self::assertInstanceOf(EmbeddingProviderInterface::class, $this->provider);
    }

    public function testVectorDimensionIsFixed(): void
    {
        $vector = $this->provider->generateEmbedding(
            ChunkText::fromString('Chunk with fixed dimension'),
        );

        self::assertSame(8, $vector->dimension());
        self::assertCount(8, $vector->values());
        foreach ($vector->values() as $value) {
            self::assertIsFloat($value);
        }
    }

    public function testOutputIsDeterministic(): void
    {
        $text = ChunkText::fromString('Deterministic chunk text');

        $firstRun = $this->provider->generateEmbedding($text);
        $secondRun = $this->provider->generateEmbedding($text);

        self::assertTrue($firstRun->equals($secondRun));
    }

    public function testDifferentTextProducesDifferentVector(): void
    {
        $firstVector = $this->provider->generateEmbedding(
            ChunkText::fromString('Roman Republic overview'),
        );
        $secondVector = $this->provider->generateEmbedding(
            ChunkText::fromString('Roman Empire overview'),
        );

        self::assertFalse($firstVector->equals($secondVector));
    }
}
