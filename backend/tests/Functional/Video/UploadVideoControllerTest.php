<?php

declare(strict_types=1);

namespace App\Tests\Functional\Video;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadVideoControllerTest extends WebTestCase
{
    public function testUploadsSupportedVideoAndReturnsCreatedStatus(): void
    {
        $client = static::createClient();
        $tempFile = tempnam(sys_get_temp_dir(), 'video-upload-');
        self::assertNotFalse($tempFile);
        file_put_contents($tempFile, str_repeat('a', 128));

        try {
            $client->request(
                'POST',
                '/api/videos',
                [],
                [
                    'video' => new UploadedFile(
                        $tempFile,
                        'lecture.mp4',
                        'video/mp4',
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
        self::assertArrayHasKey('videoId', $response);
        self::assertSame('uploaded', $response['status']);
    }

    public function testMissingFileReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/videos');

        self::assertResponseStatusCodeSame(400);
        self::assertJsonStringEqualsJsonString(
            '{"error":"Invalid request"}',
            $client->getResponse()->getContent(),
        );
    }

    public function testUnsupportedExtensionReturnsBadRequest(): void
    {
        $client = static::createClient();
        $tempFile = tempnam(sys_get_temp_dir(), 'video-upload-');
        self::assertNotFalse($tempFile);
        file_put_contents($tempFile, 'video');

        try {
            $client->request(
                'POST',
                '/api/videos',
                [],
                [
                    'video' => new UploadedFile(
                        $tempFile,
                        'lecture.avi',
                        'video/x-msvideo',
                        null,
                        true,
                    ),
                ],
            );
        } finally {
            @unlink($tempFile);
        }

        self::assertResponseStatusCodeSame(400);
    }

    public function testOversizedUploadReturnsBadRequest(): void
    {
        $client = static::createClient();
        $tempFile = tempnam(sys_get_temp_dir(), 'video-upload-');
        self::assertNotFalse($tempFile);
        file_put_contents($tempFile, str_repeat('a', 2048));

        try {
            $client->request(
                'POST',
                '/api/videos',
                [],
                [
                    'video' => new UploadedFile(
                        $tempFile,
                        'lecture.mov',
                        'video/quicktime',
                        null,
                        true,
                    ),
                ],
            );
        } finally {
            @unlink($tempFile);
        }

        self::assertResponseStatusCodeSame(400);
    }
}
