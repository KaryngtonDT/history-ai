<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\LipSync;

use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncProviderInterface;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Domain\VoiceClone\VoiceCloneArtifactId;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceProfile;
use App\Domain\VoiceClone\VoiceProfileId;
use PHPUnit\Framework\TestCase;

final class LipSyncProviderInterfaceTest extends TestCase
{
    public function testProviderInterfaceDefinesSynchronizeMethod(): void
    {
        $videoJob = VideoJob::createUploaded(
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            'lecture.mp4',
            VideoLanguage::English,
        )->withStoragePath('/var/video/lecture.mp4');

        $voiceClone = VoiceCloneArtifact::create(
            new VoiceCloneArtifactId('550e8400-e29b-41d4-a716-446655440050'),
            VoiceProfile::create(
                new VoiceProfileId('550e8400-e29b-41d4-a716-446655440040'),
                TranslationLanguage::English,
                3.5,
                44100,
            ),
            VoiceCloneProvider::OpenVoice,
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            '/tmp/cloned.wav',
            TranslationLanguage::French,
        );

        $expected = LipSyncArtifact::create(
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440080'),
            $videoJob->id(),
            $voiceClone->clonedAudioId(),
            LipSyncProvider::LatentSync,
            LipSyncVideo::create(
                new LipSyncVideoId('550e8400-e29b-41d4-a716-446655440070'),
                '/tmp/synced.mp4',
                3.5,
            ),
        );

        $provider = $this->createMock(LipSyncProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('synchronize')
            ->with($videoJob, $voiceClone)
            ->willReturn($expected);

        self::assertSame($expected, $provider->synchronize($videoJob, $voiceClone));
    }
}
