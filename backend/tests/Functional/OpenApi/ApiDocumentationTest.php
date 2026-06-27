<?php

declare(strict_types=1);

namespace App\Tests\Functional\OpenApi;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ApiDocumentationTest extends WebTestCase
{
    private const PUBLIC_PATHS = [
        '/api/contents',
        '/api/contents/{contentId}/artifacts',
        '/api/library/items',
        '/api/collections',
        '/api/collections/{collectionId}/items',
    ];

    public function testSwaggerUiIsAvailable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/docs');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('swagger-ui', (string) $client->getResponse()->getContent());
    }

    public function testOpenApiSpecGenerationSucceeds(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/docs.json');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $spec = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('3.1.0', $spec['openapi']);
        self::assertSame('History AI API', $spec['info']['title']);

        foreach (self::PUBLIC_PATHS as $path) {
            self::assertArrayHasKey($path, $spec['paths'], sprintf('Missing documented path: %s', $path));
        }

        self::assertArrayHasKey('post', $spec['paths']['/api/contents']);
        self::assertArrayHasKey('get', $spec['paths']['/api/contents']);
        self::assertArrayHasKey('get', $spec['paths']['/api/contents/{contentId}/artifacts']);
        self::assertArrayHasKey('post', $spec['paths']['/api/library/items']);
        self::assertArrayHasKey('get', $spec['paths']['/api/library/items']);
        self::assertArrayHasKey('post', $spec['paths']['/api/collections']);
        self::assertArrayHasKey('get', $spec['paths']['/api/collections']);
        self::assertArrayHasKey('post', $spec['paths']['/api/collections/{collectionId}/items']);
    }
}
