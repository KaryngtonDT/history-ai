<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\LipSync;

use App\Domain\LipSync\LipSyncProvider;
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
use App\Infrastructure\LipSync\Exception\LatentSyncProviderException;
use App\Infrastructure\LipSync\FixedLatentSyncProcessRunner;
use App\Infrastructure\LipSync\LatentSyncProvider;
use App\Infrastructure\LipSync\LipSyncMapper;
use PHPUnit\Framework\TestCase;

final class LatentSyncProviderTest extends TestCase
{
    private string $outputDirectory;

    protected function setUp(): void
    {
        $this->outputDirectory = sys_get_temp_dir().'/history-ai-lipsync-'.uniqid('', true);
        mkdir($this->outputDirectory);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->outputDirectory)) {
            foreach (glob($this->outputDirectory.'/*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->outputDirectory);
        }
    }

    public function testSynchronizeReturnsLipSyncArtifact(): void
    {
        $provider = new LatentSyncProvider(
            new FixedLatentSyncProcessRunner(),
            new LipSyncMapper(),
            'latentsync',
            'latentsync',
            '/models/latentsync',
            $this->outputDirectory,
        );

        $video = VideoJob::createUploaded(
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            'lecture.mp4',
            VideoLanguage::English,
        )->withStoragePath('/var/video/lecture.mp4');

        $artifact = $provider->synchronize($video, $this->createVoiceClone());

        self::assertSame(LipSyncProvider::LatentSync, $artifact->provider());
        self::assertTrue($artifact->sourceVideoId()->equals($video->id()));
        self::assertGreaterThan(0, $artifact->video()->duration());
        self::assertFileExists($artifact->video()->storagePath());
    }

    public function testMissingVideoPathThrows(): void
    {
        $provider = new LatentSyncProvider(
            new FixedLatentSyncProcessRunner(),
            new LipSyncMapper(),
            'latentsync',
            'latentsync',
            '/models/latentsync',
            $this->outputDirectory,
        );

        $video = VideoJob::createUploaded(
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            'lecture.mp4',
            VideoLanguage::English,
        );

        $this->expectException(LatentSyncProviderException::class);

        $provider->synchronize($video, $this->createVoiceClone());
    }

    private function createVoiceClone(): VoiceCloneArtifact
    {
        return VoiceCloneArtifact::create(
            new VoiceCloneArtifactId('550e8400-e29b-41d4-a716-446655440050'),
            VoiceProfile::create(
                new VoiceProfileId('550e8400-e29b-41d4-a716-446655440040'),
                TranslationLanguage::English,
                4.0,
                44100,
            ),
            VoiceCloneProvider::OpenVoice,
            new AudioId('550e8400-e29b-41d4-a716-446655440060'),
            new AudioId('550e8400-e29b-41d4-a716-446655440030'),
            '/tmp/cloned.wav',
            TranslationLanguage::French,
        );
    }
}
