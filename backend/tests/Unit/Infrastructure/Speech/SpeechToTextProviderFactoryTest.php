<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Speech;

use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Infrastructure\Speech\DeterministicSpeechToTextProvider;
use App\Infrastructure\Speech\Exception\InvalidSpeechToTextConfigurationException;
use App\Infrastructure\Speech\FasterWhisperOutputParser;
use App\Infrastructure\Speech\FasterWhisperProcessRunnerInterface;
use App\Infrastructure\Speech\FasterWhisperProvider;
use App\Infrastructure\Speech\SpeechToTextProviderFactory;
use PHPUnit\Framework\TestCase;

final class SpeechToTextProviderFactoryTest extends TestCase
{
    private FasterWhisperProvider $fasterWhisperProvider;
    private DeterministicSpeechToTextProvider $deterministicProvider;

    protected function setUp(): void
    {
        $parser = new FasterWhisperOutputParser();
        $this->fasterWhisperProvider = new FasterWhisperProvider(
            $this->createStub(FasterWhisperProcessRunnerInterface::class),
            $parser,
            'faster-whisper',
            'base',
        );
        $this->deterministicProvider = new DeterministicSpeechToTextProvider($parser);
    }

    private function factory(string $providerName): SpeechToTextProviderFactory
    {
        return new SpeechToTextProviderFactory(
            $providerName,
            $this->fasterWhisperProvider,
            $this->deterministicProvider,
        );
    }

    public function testCreatesFasterWhisperProviderByDefault(): void
    {
        $provider = $this->factory('')->create();

        self::assertSame($this->fasterWhisperProvider, $provider);
    }

    public function testCreatesFasterWhisperProviderForExplicitName(): void
    {
        $provider = $this->factory(SpeechToTextProviderFactory::PROVIDER_FASTER_WHISPER)->create();

        self::assertInstanceOf(SpeechToTextProviderInterface::class, $provider);
        self::assertSame($this->fasterWhisperProvider, $provider);
    }

    public function testCreatesDeterministicProviderForExplicitName(): void
    {
        $provider = $this->factory(SpeechToTextProviderFactory::PROVIDER_DETERMINISTIC)->create();

        self::assertSame($this->deterministicProvider, $provider);
    }

    public function testRejectsUnknownProvider(): void
    {
        $this->expectException(InvalidSpeechToTextConfigurationException::class);

        $this->factory('unknown')->create();
    }
}
