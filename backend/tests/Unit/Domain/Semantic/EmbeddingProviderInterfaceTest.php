<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingProviderInterface;
use App\Domain\Semantic\EmbeddingVector;
use PHPUnit\Framework\TestCase;

final class EmbeddingProviderInterfaceTest extends TestCase
{
    public function testProviderInterfaceDefinesGenerateEmbeddingMethod(): void
    {
        $text = ChunkText::fromString('## Roman Republic');
        $expected = new EmbeddingVector([0.1, 0.2]);

        $provider = $this->createMock(EmbeddingProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('generateEmbedding')
            ->with($text)
            ->willReturn($expected);

        self::assertSame($expected, $provider->generateEmbedding($text));
    }
}
