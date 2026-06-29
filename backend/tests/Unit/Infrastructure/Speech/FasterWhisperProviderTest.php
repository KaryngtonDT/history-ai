<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Speech;

use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Infrastructure\Speech\Exception\FasterWhisperProviderException;
use App\Infrastructure\Speech\FasterWhisperOutputParser;
use App\Infrastructure\Speech\FasterWhisperProcessRunnerInterface;
use App\Infrastructure\Speech\FasterWhisperProvider;
use PHPUnit\Framework\TestCase;

final class FasterWhisperProviderTest extends TestCase
{
    public function testImplementsSpeechToTextProviderInterface(): void
    {
        $provider = new FasterWhisperProvider(
            $this->createMock(FasterWhisperProcessRunnerInterface::class),
            new FasterWhisperOutputParser(),
            'faster-whisper',
            'base',
        );

        self::assertInstanceOf(
            \App\Domain\Speech\SpeechToTextProviderInterface::class,
            $provider,
        );
    }

    public function testTranscribeInvokesProcessAndMapsTranscript(): void
    {
        $runner = $this->createMock(FasterWhisperProcessRunnerInterface::class);
        $runner
            ->expects(self::once())
            ->method('run')
            ->with([
                'faster-whisper',
                '/var/video-storage/lecture.mp4',
                '--model',
                'base',
                '--output-format',
                'json',
            ])
            ->willReturn(<<<'JSON'
            {
                "language": "de",
                "segments": [
                    {"index": 0, "start": 0.0, "end": 2.0, "text": "Guten Tag"}
                ]
            }
            JSON);

        $provider = new FasterWhisperProvider(
            $runner,
            new FasterWhisperOutputParser(),
            'faster-whisper',
            'base',
        );

        $video = VideoJob::createUploaded(
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            'lecture.mp4',
            VideoLanguage::German,
        )
            ->withStoragePath('/var/video-storage/lecture.mp4')
            ->queue()
            ->startProcessing();

        $transcript = $provider->transcribe($video);

        self::assertSame(TranscriptLanguage::German, $transcript->language());
        self::assertSame('Guten Tag', $transcript->text());
    }

    public function testRejectsVideoWithoutStoragePath(): void
    {
        $provider = new FasterWhisperProvider(
            $this->createMock(FasterWhisperProcessRunnerInterface::class),
            new FasterWhisperOutputParser(),
            'faster-whisper',
            'base',
        );

        $video = VideoJob::createUploaded(
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            'lecture.mp4',
            VideoLanguage::English,
        );

        $this->expectException(FasterWhisperProviderException::class);

        $provider->transcribe($video);
    }
}
