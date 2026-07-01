<?php

declare(strict_types=1);

namespace App\Tests\Functional\YouTube;

use App\Domain\Source\SourceId;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceType;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoStatus;
use App\Domain\YouTube\YouTubeVideoId;
use App\Domain\YouTube\YouTubeVideoRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Source\SourceRecord;
use App\Infrastructure\Persistence\Doctrine\Video\VideoJobRecord;
use App\Infrastructure\Persistence\Doctrine\YouTube\YouTubeImportRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ImportYouTubeControllerTest extends WebTestCase
{
    public function testImportCreatesVideoJobAndSource(): void
    {
        $client = static::createClient();
        $this->resetDatabaseSchema();

        $client->request(
            'POST',
            '/api/youtube',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('youtubeId', $response);
        self::assertArrayHasKey('videoId', $response);
        self::assertSame('queued', $response['status']);
        self::assertSame('Mock YouTube Lecture', $response['metadata']['title']);

        $videoRepository = static::getContainer()->get(VideoRepositoryInterface::class);
        $job = $videoRepository->findById(new VideoId($response['videoId']));
        self::assertNotNull($job);
        self::assertSame(VideoStatus::Queued, $job->status());

        $sourceRepository = static::getContainer()->get(SourceRepositoryInterface::class);
        $source = $sourceRepository->findById(new SourceId($response['youtubeId']));
        self::assertNotNull($source);
        self::assertSame(SourceType::Youtube, $source->type());

        $youtubeRepository = static::getContainer()->get(YouTubeVideoRepositoryInterface::class);
        $youtube = $youtubeRepository->findById(new YouTubeVideoId($response['youtubeId']));
        self::assertNotNull($youtube);
        self::assertSame($response['videoId'], $youtube->videoId()->value);
    }

    public function testPreviewReturnsMetadata(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/youtube/preview',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'url' => 'https://youtu.be/dQw4w9WgXcQ',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('Mock YouTube Lecture', $response['metadata']['title']);
    }

    public function testInvalidUrlReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/youtube',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{"url":"https://example.com/not-youtube"}',
        );

        self::assertResponseStatusCodeSame(400);
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = [
            $entityManager->getClassMetadata(YouTubeImportRecord::class),
            $entityManager->getClassMetadata(SourceRecord::class),
            $entityManager->getClassMetadata(VideoJobRecord::class),
        ];
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
