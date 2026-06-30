<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\TTS;

use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\TextToSpeechProviderInterface;
use App\Infrastructure\TTS\AudioMapper;
use App\Infrastructure\TTS\Exception\InvalidTextToSpeechConfigurationException;
use App\Infrastructure\TTS\F5TextToSpeechProvider;
use App\Infrastructure\TTS\FixedF5ProcessRunner;
use App\Infrastructure\TTS\MockTextToSpeechProvider;
use App\Infrastructure\TTS\TextToSpeechProviderFactory;
use PHPUnit\Framework\TestCase;

final class TextToSpeechProviderFactoryTest extends TestCase
{
    private F5TextToSpeechProvider $f5Provider;
    private MockTextToSpeechProvider $mockProvider;

    protected function setUp(): void
    {
        $outputDirectory = sys_get_temp_dir().'/history-ai-tts-factory-'.uniqid('', true);
        mkdir($outputDirectory);

        $this->f5Provider = new F5TextToSpeechProvider(
            new FixedF5ProcessRunner(),
            new AudioMapper(),
            'f5-tts',
            'F5-TTS',
            '/models/f5',
            $outputDirectory,
        );
        $this->mockProvider = new MockTextToSpeechProvider();
    }

    public function testDefaultProviderIsF5(): void
    {
        $factory = new TextToSpeechProviderFactory('f5', $this->f5Provider, $this->mockProvider);

        self::assertSame($this->f5Provider, $factory->resolve(null));
    }

    public function testResolveExplicitProvider(): void
    {
        $factory = new TextToSpeechProviderFactory('f5', $this->f5Provider, $this->mockProvider);

        self::assertInstanceOf(
            TextToSpeechProviderInterface::class,
            $factory->resolve(TextToSpeechProvider::F5TTS),
        );
        self::assertSame($this->mockProvider, $factory->resolve(TextToSpeechProvider::Mock));
    }

    public function testUnimplementedProviderThrows(): void
    {
        $factory = new TextToSpeechProviderFactory('f5', $this->f5Provider, $this->mockProvider);

        $this->expectException(InvalidTextToSpeechConfigurationException::class);

        $factory->resolve(TextToSpeechProvider::Kokoro);
    }

    public function testUnknownDefaultProviderThrows(): void
    {
        $factory = new TextToSpeechProviderFactory('unknown', $this->f5Provider, $this->mockProvider);

        $this->expectException(InvalidTextToSpeechConfigurationException::class);

        $factory->resolve(null);
    }
}
