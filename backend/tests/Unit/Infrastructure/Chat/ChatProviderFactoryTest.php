<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Chat;

use App\Domain\Chat\ChatProviderInterface;
use App\Infrastructure\Chat\ChatProviderFactory;
use App\Infrastructure\Chat\Exception\InvalidChatProviderConfigurationException;
use App\Infrastructure\Chat\GeminiChatProvider;
use App\Infrastructure\Chat\GeminiChatTransportInterface;
use App\Infrastructure\Chat\MockChatProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ChatProviderFactoryTest extends TestCase
{
    private MockChatProvider $mockProvider;

    private GeminiChatTransportInterface&MockObject $transport;

    protected function setUp(): void
    {
        $this->mockProvider = new MockChatProvider();
        $this->transport = $this->createMock(GeminiChatTransportInterface::class);
        $this->transport
            ->expects(self::never())
            ->method('generateContent');
    }

    public function testSelectsMockProviderByDefault(): void
    {
        $factory = $this->createFactory('');

        $provider = $factory->create();

        self::assertSame($this->mockProvider, $provider);
    }

    public function testSelectsMockProviderExplicitly(): void
    {
        $factory = $this->createFactory('mock');

        $provider = $factory->create();

        self::assertSame($this->mockProvider, $provider);
    }

    public function testSelectsGeminiProviderWhenConfigured(): void
    {
        $transport = $this->createMock(GeminiChatTransportInterface::class);
        $transport
            ->expects(self::never())
            ->method('generateContent');

        $factory = new ChatProviderFactory(
            'gemini',
            'gemini-api-key',
            $this->mockProvider,
            $transport,
        );

        $provider = $factory->create();

        self::assertInstanceOf(GeminiChatProvider::class, $provider);
        self::assertInstanceOf(ChatProviderInterface::class, $provider);
    }

    public function testGeminiSelectionWithoutApiKeyFailsClearly(): void
    {
        $factory = $this->createFactory('gemini', '');

        $this->expectException(InvalidChatProviderConfigurationException::class);
        $this->expectExceptionMessage('GEMINI_API_KEY is required when CHAT_PROVIDER=gemini.');

        $factory->create();
    }

    public function testUnknownProviderThrowsConfigurationException(): void
    {
        $factory = $this->createFactory('openai');

        $this->expectException(InvalidChatProviderConfigurationException::class);
        $this->expectExceptionMessage('Unknown CHAT_PROVIDER value "openai". Supported values: mock, gemini.');

        $factory->create();
    }

    public function testProviderSelectionDoesNotCallNetwork(): void
    {
        $mockFactory = $this->createFactory('mock');
        $geminiFactory = $this->createFactory('gemini', 'gemini-api-key');

        $mockFactory->create();
        $geminiFactory->create();
    }

    private function createFactory(
        string $providerName,
        string $geminiApiKey = '',
    ): ChatProviderFactory {
        return new ChatProviderFactory(
            $providerName,
            $geminiApiKey,
            $this->mockProvider,
            $this->transport,
        );
    }
}
