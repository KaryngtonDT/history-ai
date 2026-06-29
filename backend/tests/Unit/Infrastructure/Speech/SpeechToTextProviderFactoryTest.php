<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Speech;

use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Infrastructure\Speech\Exception\InvalidSpeechToTextConfigurationException;
use App\Infrastructure\Speech\FasterWhisperOutputParser;
use App\Infrastructure\Speech\FasterWhisperProcessRunnerInterface;
use App\Infrastructure\Speech\FasterWhisperProvider;
use App\Infrastructure\Speech\SpeechToTextProviderFactory;
use PHPUnit\Framework\TestCase;

final class SpeechToTextProviderFactoryTest extends TestCase
{
    private FasterWhisperProvider $fasterWhisperProvider;

    protected function setUp(): void
    {
        $this->fasterWhisperProvider = new FasterWhisperProvider(
            $this->createMock(FasterWhisperProcessRunnerInterface::class),
            new FasterWhisperOutputParser(),
            'faster-whisper',
            'base',
        );
    }

    public function testCreatesFasterWhisperProviderByDefault(): void
    {
        $factory = new SpeechToTextProviderFactory('', $this->fasterWhisperProvider);

        $provider = $factory->create();

        self::assertSame($this->fasterWhisperProvider, $provider);
    }

    public function testCreatesFasterWhisperProviderForExplicitName(): void
    {
        $factory = new SpeechToTextProviderFactory(
            SpeechToTextProviderFactory::PROVIDER_FASTER_WHISPER,
            $this->fasterWhisperProvider,
        );

        $provider = $factory->create();

        self::assertInstanceOf(SpeechToTextProviderInterface::class, $provider);
        self::assertSame($this->fasterWhisperProvider, $provider);
    }

    public function testRejectsUnknownProvider(): void
    {
        $factory = new SpeechToTextProviderFactory(
            'unknown',
            $this->fasterWhisperProvider,
        );

        $this->expectException(InvalidSpeechToTextConfigurationException::class);

        $factory->create();
    }
}
