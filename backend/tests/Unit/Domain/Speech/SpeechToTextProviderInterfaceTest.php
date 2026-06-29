<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Speech;

use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Speech\Transcript;
use App\Domain\Speech\TranscriptId;
use App\Domain\Speech\TranscriptLanguage;
use App\Domain\Speech\TranscriptSegment;
use App\Domain\Speech\TranscriptSegmentCollection;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use PHPUnit\Framework\TestCase;

final class SpeechToTextProviderInterfaceTest extends TestCase
{
    public function testProviderInterfaceDefinesTranscribeMethod(): void
    {
        $video = VideoJob::createUploaded(
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            'lecture.mp4',
            VideoLanguage::English,
        )
            ->withStoragePath('/var/video-storage/lecture.mp4')
            ->queue()
            ->startProcessing();

        $expected = Transcript::create(
            new TranscriptId('550e8400-e29b-41d4-a716-446655440010'),
            TranscriptLanguage::English,
            new TranscriptSegmentCollection([
                TranscriptSegment::create(0, 0.0, 2.0, 'Hello world'),
            ]),
        );

        $provider = $this->createMock(SpeechToTextProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('transcribe')
            ->with($video)
            ->willReturn($expected);

        self::assertSame($expected, $provider->transcribe($video));
    }
}
