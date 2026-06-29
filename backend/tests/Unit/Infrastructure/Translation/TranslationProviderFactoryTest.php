<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Translation;

use App\Domain\Translation\TranslationProvider;
use App\Infrastructure\Translation\Exception\InvalidTranslationConfigurationException;
use App\Infrastructure\Translation\MockTranslationProvider;
use App\Infrastructure\Translation\OllamaTranslationPromptBuilder;
use App\Infrastructure\Translation\OllamaTranslationProvider;
use App\Infrastructure\Translation\FixedOllamaClient;
use App\Infrastructure\Translation\TranslationProviderFactory;
use PHPUnit\Framework\TestCase;

final class TranslationProviderFactoryTest extends TestCase
{
    private OllamaTranslationProvider $ollamaProvider;
    private MockTranslationProvider $mockProvider;

    protected function setUp(): void
    {
        $this->ollamaProvider = new OllamaTranslationProvider(
            new FixedOllamaClient(),
            new OllamaTranslationPromptBuilder(),
            'qwen3',
        );
        $this->mockProvider = new MockTranslationProvider();
    }

    public function testCreatesOllamaProviderByDefault(): void
    {
        $factory = new TranslationProviderFactory('ollama', $this->ollamaProvider, $this->mockProvider);

        self::assertSame($this->ollamaProvider, $factory->create());
    }

    public function testCreatesProviderByEnum(): void
    {
        $factory = new TranslationProviderFactory('ollama', $this->ollamaProvider, $this->mockProvider);

        self::assertSame($this->mockProvider, $factory->create(TranslationProvider::Mock));
        self::assertSame($this->ollamaProvider, $factory->create(TranslationProvider::Qwen));
    }

    public function testRejectsUnknownDefaultProvider(): void
    {
        $factory = new TranslationProviderFactory('unknown', $this->ollamaProvider, $this->mockProvider);

        $this->expectException(InvalidTranslationConfigurationException::class);

        $factory->create();
    }

    public function testRejectsUnimplementedProvider(): void
    {
        $factory = new TranslationProviderFactory('ollama', $this->ollamaProvider, $this->mockProvider);

        $this->expectException(InvalidTranslationConfigurationException::class);

        $factory->create(TranslationProvider::Gemini);
    }
}
