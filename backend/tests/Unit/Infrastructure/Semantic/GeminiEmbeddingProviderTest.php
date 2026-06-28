<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Semantic;

use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingProviderInterface;
use App\Domain\Semantic\EmbeddingVector;
use App\Infrastructure\Semantic\Exception\GeminiEmbeddingProviderException;
use App\Infrastructure\Semantic\GeminiEmbeddingProvider;
use App\Infrastructure\Semantic\GeminiEmbeddingTransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class GeminiEmbeddingProviderTest extends TestCase
{
    private GeminiEmbeddingTransportInterface&MockObject $transport;

    protected function setUp(): void
    {
        $this->transport = $this->createMock(GeminiEmbeddingTransportInterface::class);
    }

    public function testImplementsEmbeddingProviderInterface(): void
    {
        $this->transport
            ->expects(self::never())
            ->method('post');

        self::assertInstanceOf(
            EmbeddingProviderInterface::class,
            new GeminiEmbeddingProvider($this->transport, 'test-api-key'),
        );
    }

    public function testSendsExpectedRequestPayload(): void
    {
        $text = ChunkText::fromString('Roman Republic overview');
        $expectedUrl = 'https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent';
        $expectedPayload = [
            'model' => 'models/text-embedding-004',
            'content' => [
                'parts' => [
                    ['text' => 'Roman Republic overview'],
                ],
            ],
        ];

        $this->transport
            ->expects(self::once())
            ->method('post')
            ->with(
                $expectedUrl,
                self::callback(static function (array $headers): bool {
                    return 'application/json' === ($headers['Content-Type'] ?? null)
                        && 'test-api-key' === ($headers['x-goog-api-key'] ?? null);
                }),
                $expectedPayload,
            )
            ->willReturn(json_encode([
                'embedding' => ['values' => [0.1, 0.2, 0.3]],
            ], JSON_THROW_ON_ERROR));

        $vector = $this->createProvider()->generateEmbedding($text);

        self::assertSame([0.1, 0.2, 0.3], $vector->values());
    }

    public function testMapsValidResponseToEmbeddingVector(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('post')
            ->willReturn(json_encode([
                'embedding' => ['values' => [0.5, -0.25, 1.0]],
            ], JSON_THROW_ON_ERROR));

        $vector = $this->createProvider()->generateEmbedding(
            ChunkText::fromString('Valid embedding response'),
        );

        self::assertInstanceOf(EmbeddingVector::class, $vector);
        self::assertSame([0.5, -0.25, 1.0], $vector->values());
        self::assertSame(3, $vector->dimension());
    }

    public function testRejectsEmptyEmbeddingResponse(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('post')
            ->willReturn(json_encode([
                'embedding' => ['values' => []],
            ], JSON_THROW_ON_ERROR));

        $this->expectException(GeminiEmbeddingProviderException::class);
        $this->expectExceptionMessage('empty embedding');

        $this->createProvider()->generateEmbedding(
            ChunkText::fromString('Empty embedding response'),
        );
    }

    public function testRejectsMissingEmbeddingResponse(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('post')
            ->willReturn(json_encode([], JSON_THROW_ON_ERROR));

        $this->expectException(GeminiEmbeddingProviderException::class);
        $this->expectExceptionMessage('no embedding');

        $this->createProvider()->generateEmbedding(
            ChunkText::fromString('Missing embedding response'),
        );
    }

    public function testRejectsNonNumericEmbeddingValues(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('post')
            ->willReturn(json_encode([
                'embedding' => ['values' => [0.1, 'not-a-number', 0.3]],
            ], JSON_THROW_ON_ERROR));

        $this->expectException(GeminiEmbeddingProviderException::class);
        $this->expectExceptionMessage('invalid embedding values');

        $this->createProvider()->generateEmbedding(
            ChunkText::fromString('Invalid embedding values'),
        );
    }

    public function testWrapsTransportErrorsInGeminiEmbeddingProviderException(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('post')
            ->willThrowException(new RuntimeException('Connection refused'));

        $this->expectException(GeminiEmbeddingProviderException::class);
        $this->expectExceptionMessage('Gemini embedding request failed.');

        $this->createProvider()->generateEmbedding(
            ChunkText::fromString('Transport failure'),
        );
    }

    public function testRequiresConfiguredApiKey(): void
    {
        $this->transport
            ->expects(self::never())
            ->method('post');

        $provider = new GeminiEmbeddingProvider(
            $this->transport,
            '',
            GeminiEmbeddingProvider::DEFAULT_MODEL,
        );

        $this->expectException(GeminiEmbeddingProviderException::class);
        $this->expectExceptionMessage('GEMINI_API_KEY is not configured.');

        $provider->generateEmbedding(ChunkText::fromString('Missing API key'));
    }

    public function testDoesNotCallTransportWhenApiKeyMissing(): void
    {
        $this->transport
            ->expects(self::never())
            ->method('post');

        $provider = new GeminiEmbeddingProvider(
            $this->transport,
            '   ',
            GeminiEmbeddingProvider::DEFAULT_MODEL,
        );

        $this->expectException(GeminiEmbeddingProviderException::class);

        $provider->generateEmbedding(ChunkText::fromString('Missing API key'));
    }

    public function testUsesConfiguredModelInRequestUrl(): void
    {
        $this->transport
            ->expects(self::once())
            ->method('post')
            ->with(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:embedContent',
                self::anything(),
                self::callback(static function (array $payload): bool {
                    return 'models/gemini-embedding-001' === ($payload['model'] ?? null);
                }),
            )
            ->willReturn(json_encode([
                'embedding' => ['values' => [0.1]],
            ], JSON_THROW_ON_ERROR));

        $provider = new GeminiEmbeddingProvider(
            $this->transport,
            'test-api-key',
            'gemini-embedding-001',
        );

        $provider->generateEmbedding(ChunkText::fromString('Custom model'));
    }

    private function createProvider(): GeminiEmbeddingProvider
    {
        return new GeminiEmbeddingProvider(
            $this->transport,
            'test-api-key',
            GeminiEmbeddingProvider::DEFAULT_MODEL,
        );
    }
}
