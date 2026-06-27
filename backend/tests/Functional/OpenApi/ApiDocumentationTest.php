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
        '/api/search/library',
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
        self::assertArrayHasKey('get', $spec['paths']['/api/search/library']);
    }

    public function testOpenApiSpecDocumentsSearchLibraryQueryParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/search/library']['get'];

        self::assertSame('searchLibrary', $operation['operationId']);

        $queryParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'q') {
                $queryParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($queryParameter, 'Missing query parameter: q');
        self::assertSame('query', $queryParameter['in']);
        self::assertTrue($queryParameter['required']);
        self::assertSame('string', $queryParameter['schema']['type']);
        self::assertSame(1, $queryParameter['schema']['minLength']);
        self::assertSame(255, $queryParameter['schema']['maxLength']);
        self::assertSame('roman', $queryParameter['schema']['example']);
    }

    public function testOpenApiSpecDocumentsSearchLibraryResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/search/library']['get']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Search results', $responses['200']['description']);
        self::assertSame('array', $responses['200']['content']['application/json']['schema']['type']);
        self::assertSame(
            '#/components/schemas/SearchLibraryItem',
            $responses['200']['content']['application/json']['schema']['items']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsArtifactTypeSchemaIncludesTimeline(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $artifactType = $spec['components']['schemas']['ArtifactType'];

        self::assertSame('string', $artifactType['type']);
        self::assertContains('timeline', $artifactType['enum']);
        self::assertSame('timeline', $artifactType['example']);
    }

    public function testOpenApiSpecDocumentsLibraryItemTypeSchemaIncludesTimeline(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $libraryItemType = $spec['components']['schemas']['LibraryItemType'];

        self::assertSame('string', $libraryItemType['type']);
        self::assertContains('timeline', $libraryItemType['enum']);
        self::assertSame('timeline', $libraryItemType['example']);
    }

    public function testOpenApiSpecDocumentsArtifactSchemaUsesArtifactType(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $artifactSchema = $spec['components']['schemas']['Artifact'];

        self::assertSame(
            '#/components/schemas/ArtifactType',
            $artifactSchema['properties']['type']['$ref'],
        );
        self::assertStringContainsString('Timeline', $artifactSchema['properties']['content']['example']);
    }

    public function testOpenApiSpecDocumentsAddLibraryItemRequestUsesLibraryItemType(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $typeProperty = $spec['paths']['/api/library/items']['post']['requestBody']['content']['application/json']['schema']['properties']['type'];

        self::assertSame('#/components/schemas/LibraryItemType', $typeProperty['$ref']);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchOpenApiSpec(): array
    {
        $client = static::createClient();
        $client->request('GET', '/api/docs.json');

        self::assertResponseIsSuccessful();

        return json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}
