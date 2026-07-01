<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\AudioUpload;

use App\Application\AudioUpload\Commands\UploadAudioCommand;
use App\Application\AudioUpload\Handlers\UploadAudioHandler;
use App\Application\AudioUpload\Ports\AudioProcessingQueueInterface;
use App\Application\AudioUpload\Ports\AudioStorageInterface;
use App\Domain\Source\Exception\InvalidSourceException;
use App\Domain\Source\Source;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceStatus;
use PHPUnit\Framework\TestCase;

final class UploadAudioHandlerTest extends TestCase
{
    public function testStoresPersistsAndQueuesAudioSource(): void
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'audio-upload-');
        self::assertNotFalse($temporaryPath);
        file_put_contents($temporaryPath, str_repeat('a', 128));

        $audioStorage = $this->createMock(AudioStorageInterface::class);
        $sourceRepository = $this->createMock(SourceRepositoryInterface::class);
        $audioProcessingQueue = $this->createMock(AudioProcessingQueueInterface::class);

        $audioStorage
            ->expects(self::once())
            ->method('store')
            ->willReturnCallback(static function (SourceId $audioId): string {
                return sprintf('/var/audio-source-storage/%s.mp3', $audioId->value);
            });

        $sourceRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Source $source): bool {
                return SourceStatus::Queued === $source->status()
                    && str_ends_with($source->storagePath() ?? '', '.mp3');
            }));

        $audioProcessingQueue
            ->expects(self::once())
            ->method('enqueue')
            ->with(self::isInstanceOf(SourceId::class));

        $handler = new UploadAudioHandler(
            maxUploadBytes: 1024,
            audioStorage: $audioStorage,
            sourceRepository: $sourceRepository,
            audioProcessingQueue: $audioProcessingQueue,
        );

        $result = ($handler)(new UploadAudioCommand(
            originalFilename: 'podcast.mp3',
            fileSizeBytes: 512,
            temporaryPath: $temporaryPath,
        ));

        self::assertTrue(SourceId::isValid($result->audioId->value));
        self::assertSame(SourceStatus::Queued, $result->status);

        @unlink($temporaryPath);
    }

    public function testRejectsUnsupportedFormat(): void
    {
        $handler = new UploadAudioHandler(
            maxUploadBytes: 1024,
            audioStorage: $this->createStub(AudioStorageInterface::class),
            sourceRepository: $this->createStub(SourceRepositoryInterface::class),
            audioProcessingQueue: $this->createStub(AudioProcessingQueueInterface::class),
        );

        $this->expectException(InvalidSourceException::class);

        ($handler)(new UploadAudioCommand(
            originalFilename: 'notes.txt',
            fileSizeBytes: 10,
            temporaryPath: '/tmp/notes.txt',
        ));
    }
}
