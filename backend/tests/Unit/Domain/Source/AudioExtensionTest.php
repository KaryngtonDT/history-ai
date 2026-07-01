<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Source;

use App\Domain\Source\AudioExtension;
use App\Domain\Source\Exception\InvalidSourceException;
use App\Domain\Source\SourceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AudioExtensionTest extends TestCase
{
    #[DataProvider('supportedFilenamesProvider')]
    public function testFromFilenameAcceptsSupportedFormats(string $filename, AudioExtension $expected): void
    {
        self::assertSame($expected, AudioExtension::fromFilename($filename));
    }

    /**
     * @return iterable<string, array{string, AudioExtension}>
     */
    public static function supportedFilenamesProvider(): iterable
    {
        yield 'mp3' => ['episode.mp3', AudioExtension::Mp3];
        yield 'wav' => ['interview.WAV', AudioExtension::Wav];
        yield 'flac' => ['lecture.flac', AudioExtension::Flac];
        yield 'm4a' => ['podcast.m4a', AudioExtension::M4a];
        yield 'ogg' => ['show.ogg', AudioExtension::Ogg];
    }

    public function testRejectsUnsupportedExtension(): void
    {
        $this->expectException(InvalidSourceException::class);
        $this->expectExceptionMessage('Audio upload must use one of the supported formats');

        AudioExtension::fromFilename('notes.txt');
    }

    public function testAudioTypeIsImplementedConnector(): void
    {
        self::assertTrue(SourceType::Audio->isConnectorImplemented());
        self::assertFalse(SourceType::Pdf->isConnectorImplemented());
        self::assertTrue(SourceType::Youtube->isConnectorImplemented());
    }
}
