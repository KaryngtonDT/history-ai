<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Semantic;

use App\Domain\Semantic\EmbeddingProviderInterface;
use App\Infrastructure\Semantic\DeterministicEmbeddingProvider;
use App\Infrastructure\Semantic\EmbeddingProviderFactory;
use App\Infrastructure\Semantic\Exception\InvalidEmbeddingProviderConfigurationException;
use App\Infrastructure\Semantic\GeminiEmbeddingProvider;
use App\Infrastructure\Semantic\GeminiEmbeddingTransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EmbeddingProviderFactoryTest extends TestCase
{
    private DeterministicEmbeddingProvider $deterministicProvider;

    private GeminiEmbeddingTransportInterface&MockObject $transport;

    protected function setUp(): void
    {
        $this->deterministicProvider = new DeterministicEmbeddingProvider();
        $this->transport = $this->createMock(GeminiEmbeddingTransportInterface::class);
        $this->transport
            ->expects(self::never())
            ->method('post');
    }

    public function testSelectsDeterministicProviderByDefault(): void
    {
        $factory = $this->createFactory('');

        $provider = $factory->create();

        self::assertSame($this->deterministicProvider, $provider);
    }

    public function testSelectsDeterministicProviderExplicitly(): void
    {
        $factory = $this->createFactory('deterministic');

        $provider = $factory->create();

        self::assertSame($this->deterministicProvider, $provider);
    }

    public function testSelectsGeminiProviderWhenConfigured(): void
    {
        $transport = $this->createMock(GeminiEmbeddingTransportInterface::class);
        $transport
            ->expects(self::never())
            ->method('post');

        $factory = new EmbeddingProviderFactory(
            'gemini',
            'gemini-api-key',
            $this->deterministicProvider,
            $transport,
        );

        $provider = $factory->create();

        self::assertInstanceOf(GeminiEmbeddingProvider::class, $provider);
        self::assertInstanceOf(EmbeddingProviderInterface::class, $provider);
    }

    public function testGeminiSelectionWithoutApiKeyFailsClearly(): void
    {
        $factory = $this->createFactory('gemini', '');

        $this->expectException(InvalidEmbeddingProviderConfigurationException::class);
        $this->expectExceptionMessage('GEMINI_API_KEY is required when EMBEDDING_PROVIDER=gemini.');

        $factory->create();
    }

    public function testUnknownProviderThrowsConfigurationException(): void
    {
        $factory = $this->createFactory('openai');

        $this->expectException(InvalidEmbeddingProviderConfigurationException::class);
        $this->expectExceptionMessage('Unknown EMBEDDING_PROVIDER value "openai". Supported values: deterministic, gemini.');

        $factory->create();
    }

    public function testProviderSelectionDoesNotCallNetwork(): void
    {
        $deterministicFactory = $this->createFactory('deterministic');
        $geminiFactory = $this->createFactory('gemini', 'gemini-api-key');

        $deterministicFactory->create();
        $geminiFactory->create();
    }

    private function createFactory(
        string $providerName,
        string $geminiApiKey = '',
    ): EmbeddingProviderFactory {
        return new EmbeddingProviderFactory(
            $providerName,
            $geminiApiKey,
            $this->deterministicProvider,
            $this->transport,
        );
    }
}
