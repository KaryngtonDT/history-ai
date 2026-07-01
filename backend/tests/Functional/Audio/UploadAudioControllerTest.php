<?php

declare(strict_types=1);

namespace App\Tests\Functional\Audio;

use App\Domain\Source\SourceId;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceStatus;
use App\Infrastructure\Persistence\Doctrine\Source\SourceRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadAudioControllerTest extends WebTestCase
{
    public function testUploadsSupportedAudioAndReturnsCreatedStatus(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $tempFile = tempnam(sys_get_temp_dir(), 'audio-upload-');
        self::assertNotFalse($tempFile);
        file_put_contents($tempFile, str_repeat('a', 128));

        try {
            $client->request(
                'POST',
                '/api/audio',
                [],
                [
                    'audio' => new UploadedFile(
                        $tempFile,
                        'podcast.mp3',
                        'audio/mpeg',
                        null,
                        true,
                    ),
                ],
            );
        } finally {
            @unlink($tempFile);
        }

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('audioId', $response);
        self::assertSame('queued', $response['status']);

        $repository = static::getContainer()->get(SourceRepositoryInterface::class);
        $source = $repository->findById(new SourceId($response['audioId']));

        self::assertNotNull($source);
        self::assertContains($source->status(), [
            SourceStatus::Queued,
            SourceStatus::Processing,
            SourceStatus::Completed,
        ]);
        self::assertSame('podcast.mp3', $source->metadata()->originalFilename);
        self::assertNotNull($source->storagePath());
        self::assertFileExists($source->storagePath());
    }

    public function testGetAudioReturnsMetadata(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $tempFile = tempnam(sys_get_temp_dir(), 'audio-upload-');
        self::assertNotFalse($tempFile);
        file_put_contents($tempFile, str_repeat('a', 128));

        try {
            $client->request(
                'POST',
                '/api/audio',
                [],
                [
                    'audio' => new UploadedFile(
                        $tempFile,
                        'episode.wav',
                        'audio/wav',
                        null,
                        true,
                    ),
                ],
            );
        } finally {
            @unlink($tempFile);
        }

        $upload = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $client->request('GET', '/api/audio/'.$upload['audioId']);
        self::assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($upload['audioId'], $response['audioId']);
        self::assertSame('episode', $response['title']);
        self::assertSame('audio', $response['type']);
    }

    public function testMissingFileReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/audio');

        self::assertResponseStatusCodeSame(400);
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = [$entityManager->getClassMetadata(SourceRecord::class)];
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
