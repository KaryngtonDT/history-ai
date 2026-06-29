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
        '/api/timeline/{artifactId}',
        '/api/maps/timeline/{artifactId}',
        '/api/contents/{contentId}/relations',
        '/api/contents/{contentId}/graph',
        '/api/contents/{contentId}/graph/artifacts/{artifactId}/neighborhood',
        '/api/contents/{contentId}/artifacts/{artifactId}/recommendations',
        '/api/contents/{contentId}/semantic-search',
        '/api/contents/{contentId}/agent/run',
        '/api/contents/{contentId}/chat',
        '/api/contents/{contentId}/chat/stream',
        '/api/contents/{contentId}/conversations/{conversationId}/chat',
        '/api/contents/{contentId}/conversations/{conversationId}/chat/stream',
        '/api/conversations/{conversationId}/documents',
        '/api/conversations/{conversationId}/graph',
        '/internal/platform/metrics',
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
        self::assertArrayHasKey('get', $spec['paths']['/api/timeline/{artifactId}']);
        self::assertArrayHasKey('get', $spec['paths']['/api/maps/timeline/{artifactId}']);
        self::assertArrayHasKey('get', $spec['paths']['/api/contents/{contentId}/relations']);
        self::assertArrayHasKey('get', $spec['paths']['/api/contents/{contentId}/graph']);
        self::assertArrayHasKey(
            'get',
            $spec['paths']['/api/contents/{contentId}/graph/artifacts/{artifactId}/neighborhood'],
        );
        self::assertArrayHasKey(
            'get',
            $spec['paths']['/api/contents/{contentId}/artifacts/{artifactId}/recommendations'],
        );
        self::assertArrayHasKey('get', $spec['paths']['/api/contents/{contentId}/semantic-search']);
        self::assertArrayHasKey('post', $spec['paths']['/api/contents/{contentId}/agent/run']);
        self::assertArrayHasKey('post', $spec['paths']['/api/contents/{contentId}/chat']);
        self::assertArrayHasKey('post', $spec['paths']['/api/contents/{contentId}/chat/stream']);
        self::assertArrayHasKey(
            'post',
            $spec['paths']['/api/contents/{contentId}/conversations/{conversationId}/chat'],
        );
        self::assertArrayHasKey(
            'post',
            $spec['paths']['/api/contents/{contentId}/conversations/{conversationId}/chat/stream'],
        );
        self::assertArrayHasKey('put', $spec['paths']['/api/conversations/{conversationId}/documents']);
        self::assertArrayHasKey('get', $spec['paths']['/api/conversations/{conversationId}/graph']);
        self::assertArrayHasKey('get', $spec['paths']['/internal/platform/metrics']);
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

    public function testOpenApiSpecDocumentsGetTimelinePathParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/timeline/{artifactId}']['get'];

        self::assertSame('getTimeline', $operation['operationId']);

        $pathParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'artifactId') {
                $pathParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($pathParameter, 'Missing path parameter: artifactId');
        self::assertSame('path', $pathParameter['in']);
        self::assertTrue($pathParameter['required']);
        self::assertSame('string', $pathParameter['schema']['type']);
        self::assertSame('uuid', $pathParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $pathParameter['example']);
    }

    public function testOpenApiSpecDocumentsGetTimelineResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/timeline/{artifactId}']['get']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Structured timeline', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/Timeline',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('404', $responses);
        self::assertSame('Timeline artifact not found', $responses['404']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['404']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsTimelineSchema(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $timelineSchema = $spec['components']['schemas']['Timeline'];
        $sectionSchema = $spec['components']['schemas']['TimelineSection'];
        $eventSchema = $spec['components']['schemas']['TimelineEvent'];

        self::assertContains('sections', $timelineSchema['required']);
        self::assertSame('array', $timelineSchema['properties']['sections']['type']);
        self::assertSame(
            '#/components/schemas/TimelineSection',
            $timelineSchema['properties']['sections']['items']['$ref'],
        );

        self::assertContains('title', $sectionSchema['required']);
        self::assertContains('events', $sectionSchema['required']);
        self::assertSame(
            '#/components/schemas/TimelineEvent',
            $sectionSchema['properties']['events']['items']['$ref'],
        );

        self::assertContains('text', $eventSchema['required']);
        self::assertSame('string', $eventSchema['properties']['text']['type']);
    }

    public function testOpenApiSpecDocumentsGetTimelineMapPathParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/maps/timeline/{artifactId}']['get'];

        self::assertSame('getTimelineMap', $operation['operationId']);

        $pathParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'artifactId') {
                $pathParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($pathParameter, 'Missing path parameter: artifactId');
        self::assertSame('path', $pathParameter['in']);
        self::assertTrue($pathParameter['required']);
        self::assertSame('string', $pathParameter['schema']['type']);
        self::assertSame('uuid', $pathParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $pathParameter['example']);
    }

    public function testOpenApiSpecDocumentsGetTimelineMapResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/maps/timeline/{artifactId}']['get']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Historical map projection', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/Map',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('404', $responses);
        self::assertSame('Timeline artifact not found', $responses['404']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['404']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsMapSchemas(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('Map', $spec['components']['schemas']);
        self::assertArrayHasKey('HistoricalPlace', $spec['components']['schemas']);
        self::assertArrayHasKey('Coordinates', $spec['components']['schemas']);

        $mapSchema = $spec['components']['schemas']['Map'];
        $placeSchema = $spec['components']['schemas']['HistoricalPlace'];
        $coordinatesSchema = $spec['components']['schemas']['Coordinates'];

        self::assertArrayHasKey('places', $mapSchema['properties']);
        self::assertSame('array', $mapSchema['properties']['places']['type']);
        self::assertSame(
            '#/components/schemas/HistoricalPlace',
            $mapSchema['properties']['places']['items']['$ref'],
        );
        if (isset($mapSchema['required'])) {
            self::assertContains('places', $mapSchema['required']);
        }

        self::assertArrayHasKey('name', $placeSchema['properties']);
        self::assertArrayHasKey('coordinates', $placeSchema['properties']);
        self::assertSame(
            '#/components/schemas/Coordinates',
            $placeSchema['properties']['coordinates']['$ref'],
        );
        if (isset($placeSchema['required'])) {
            self::assertContains('name', $placeSchema['required']);
            self::assertContains('coordinates', $placeSchema['required']);
        }

        self::assertArrayHasKey('latitude', $coordinatesSchema['properties']);
        self::assertArrayHasKey('longitude', $coordinatesSchema['properties']);
        self::assertSame('number', $coordinatesSchema['properties']['latitude']['type']);
        self::assertSame('number', $coordinatesSchema['properties']['longitude']['type']);
        if (isset($coordinatesSchema['required'])) {
            self::assertContains('latitude', $coordinatesSchema['required']);
            self::assertContains('longitude', $coordinatesSchema['required']);
        }
    }

    public function testOpenApiSpecDocumentsGetArtifactRelationsPathParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/relations']['get'];

        self::assertSame('getArtifactRelations', $operation['operationId']);

        $pathParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'contentId') {
                $pathParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($pathParameter, 'Missing path parameter: contentId');
        self::assertSame('path', $pathParameter['in']);
        self::assertTrue($pathParameter['required']);
        self::assertSame('string', $pathParameter['schema']['type']);
        self::assertSame('uuid', $pathParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $pathParameter['example']);
    }

    public function testOpenApiSpecDocumentsGetArtifactRelationsResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/contents/{contentId}/relations']['get']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Artifact relations projection', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/ArtifactRelations',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsArtifactRelationSchemas(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('ArtifactRelation', $spec['components']['schemas']);
        self::assertArrayHasKey('ArtifactRelations', $spec['components']['schemas']);
        self::assertArrayHasKey('ArtifactRelationType', $spec['components']['schemas']);

        $relationSchema = $spec['components']['schemas']['ArtifactRelation'];
        $relationsSchema = $spec['components']['schemas']['ArtifactRelations'];
        $relationTypeSchema = $spec['components']['schemas']['ArtifactRelationType'];

        self::assertArrayHasKey('sourceArtifactId', $relationSchema['properties']);
        self::assertArrayHasKey('targetArtifactId', $relationSchema['properties']);
        self::assertArrayHasKey('type', $relationSchema['properties']);
        self::assertSame(
            '#/components/schemas/ArtifactRelationType',
            $relationSchema['properties']['type']['$ref'],
        );
        if (isset($relationSchema['required'])) {
            self::assertContains('sourceArtifactId', $relationSchema['required']);
            self::assertContains('targetArtifactId', $relationSchema['required']);
            self::assertContains('type', $relationSchema['required']);
        }

        self::assertArrayHasKey('relations', $relationsSchema['properties']);
        self::assertSame('array', $relationsSchema['properties']['relations']['type']);
        self::assertSame(
            '#/components/schemas/ArtifactRelation',
            $relationsSchema['properties']['relations']['items']['$ref'],
        );
        if (isset($relationsSchema['required'])) {
            self::assertContains('relations', $relationsSchema['required']);
        }

        self::assertSame('string', $relationTypeSchema['type']);
        self::assertSame(
            ['related', 'derived_from', 'references', 'next', 'previous'],
            $relationTypeSchema['enum'],
        );
        self::assertSame('derived_from', $relationTypeSchema['example']);
    }

    public function testOpenApiSpecDocumentsGetKnowledgeGraphPathParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/graph']['get'];

        self::assertSame('getKnowledgeGraph', $operation['operationId']);

        $pathParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'contentId') {
                $pathParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($pathParameter, 'Missing path parameter: contentId');
        self::assertSame('path', $pathParameter['in']);
        self::assertTrue($pathParameter['required']);
        self::assertSame('string', $pathParameter['schema']['type']);
        self::assertSame('uuid', $pathParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $pathParameter['example']);
    }

    public function testOpenApiSpecDocumentsGetKnowledgeGraphResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/contents/{contentId}/graph']['get']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Knowledge graph projection', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/KnowledgeGraph',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsKnowledgeGraphSchemas(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('KnowledgeGraph', $spec['components']['schemas']);
        self::assertArrayHasKey('GraphNode', $spec['components']['schemas']);
        self::assertArrayHasKey('GraphEdge', $spec['components']['schemas']);

        $graphSchema = $spec['components']['schemas']['KnowledgeGraph'];
        $nodeSchema = $spec['components']['schemas']['GraphNode'];
        $edgeSchema = $spec['components']['schemas']['GraphEdge'];

        self::assertArrayHasKey('nodes', $graphSchema['properties']);
        self::assertArrayHasKey('edges', $graphSchema['properties']);
        self::assertSame('array', $graphSchema['properties']['nodes']['type']);
        self::assertSame('array', $graphSchema['properties']['edges']['type']);
        self::assertSame(
            '#/components/schemas/GraphNode',
            $graphSchema['properties']['nodes']['items']['$ref'],
        );
        self::assertSame(
            '#/components/schemas/GraphEdge',
            $graphSchema['properties']['edges']['items']['$ref'],
        );
        if (isset($graphSchema['required'])) {
            self::assertContains('nodes', $graphSchema['required']);
            self::assertContains('edges', $graphSchema['required']);
        }

        self::assertArrayHasKey('artifactId', $nodeSchema['properties']);
        self::assertArrayHasKey('type', $nodeSchema['properties']);
        self::assertArrayHasKey('title', $nodeSchema['properties']);
        self::assertSame(
            '#/components/schemas/ArtifactType',
            $nodeSchema['properties']['type']['$ref'],
        );
        if (isset($nodeSchema['required'])) {
            self::assertContains('artifactId', $nodeSchema['required']);
            self::assertContains('type', $nodeSchema['required']);
            self::assertContains('title', $nodeSchema['required']);
        }

        self::assertArrayHasKey('sourceArtifactId', $edgeSchema['properties']);
        self::assertArrayHasKey('targetArtifactId', $edgeSchema['properties']);
        self::assertArrayHasKey('type', $edgeSchema['properties']);
        self::assertArrayHasKey('weight', $edgeSchema['properties']);
        self::assertSame('number', $edgeSchema['properties']['weight']['type']);
        self::assertSame('float', $edgeSchema['properties']['weight']['format']);
        self::assertSame(
            '#/components/schemas/ArtifactRelationType',
            $edgeSchema['properties']['type']['$ref'],
        );
        if (isset($edgeSchema['required'])) {
            self::assertContains('sourceArtifactId', $edgeSchema['required']);
            self::assertContains('targetArtifactId', $edgeSchema['required']);
            self::assertContains('type', $edgeSchema['required']);
        }
    }

    public function testOpenApiSpecDocumentsGetGraphNeighborhoodPathParameters(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/graph/artifacts/{artifactId}/neighborhood']['get'];

        self::assertSame('getGraphNeighborhood', $operation['operationId']);

        $parameterNames = array_map(
            static fn (array $parameter): string => $parameter['name'],
            $operation['parameters'],
        );

        self::assertSame(['contentId', 'artifactId'], $parameterNames);
    }

    public function testOpenApiSpecDocumentsGetGraphNeighborhoodResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/contents/{contentId}/graph/artifacts/{artifactId}/neighborhood']['get']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Direct artifact neighborhood', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/GraphNeighborhood',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('404', $responses);
        self::assertSame('Artifact not found', $responses['404']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['404']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsGraphNeighborhoodSchemas(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('GraphNeighborhood', $spec['components']['schemas']);
        self::assertArrayHasKey('GraphNeighborhoodNode', $spec['components']['schemas']);

        $neighborhoodSchema = $spec['components']['schemas']['GraphNeighborhood'];
        $nodeSchema = $spec['components']['schemas']['GraphNeighborhoodNode'];

        self::assertSame(
            '#/components/schemas/GraphNeighborhoodNode',
            $neighborhoodSchema['properties']['center']['$ref'],
        );
        self::assertSame(
            '#/components/schemas/GraphNeighborhoodNode',
            $neighborhoodSchema['properties']['neighbors']['items']['$ref'],
        );
        self::assertSame(
            '#/components/schemas/GraphEdge',
            $neighborhoodSchema['properties']['edges']['items']['$ref'],
        );
        if (isset($neighborhoodSchema['required'])) {
            self::assertContains('center', $neighborhoodSchema['required']);
            self::assertContains('neighbors', $neighborhoodSchema['required']);
            self::assertContains('edges', $neighborhoodSchema['required']);
        }

        self::assertArrayHasKey('artifactId', $nodeSchema['properties']);
        self::assertArrayHasKey('type', $nodeSchema['properties']);
        self::assertArrayHasKey('label', $nodeSchema['properties']);
        self::assertSame(
            '#/components/schemas/ArtifactType',
            $nodeSchema['properties']['type']['$ref'],
        );
        if (isset($nodeSchema['required'])) {
            self::assertContains('artifactId', $nodeSchema['required']);
            self::assertContains('type', $nodeSchema['required']);
            self::assertContains('label', $nodeSchema['required']);
        }
    }

    public function testOpenApiSpecDocumentsGetConversationKnowledgeGraphPathParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/conversations/{conversationId}/graph']['get'];

        self::assertSame('getConversationKnowledgeGraph', $operation['operationId']);

        $pathParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'conversationId') {
                $pathParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($pathParameter, 'Missing path parameter: conversationId');
        self::assertSame('path', $pathParameter['in']);
        self::assertTrue($pathParameter['required']);
        self::assertSame('string', $pathParameter['schema']['type']);
        self::assertSame('uuid', $pathParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440001', $pathParameter['example']);
    }

    public function testOpenApiSpecDocumentsGetConversationKnowledgeGraphResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/conversations/{conversationId}/graph']['get']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Conversation-scoped knowledge graph projection', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/KnowledgeGraph',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('404', $responses);
        self::assertSame('Conversation not found', $responses['404']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['404']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsGetArtifactRecommendationsPathParameters(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/artifacts/{artifactId}/recommendations']['get'];

        self::assertSame('getArtifactRecommendations', $operation['operationId']);

        $contentIdParameter = null;
        $artifactIdParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'contentId') {
                $contentIdParameter = $parameter;
            }

            if (($parameter['name'] ?? null) === 'artifactId') {
                $artifactIdParameter = $parameter;
            }
        }

        self::assertNotNull($contentIdParameter, 'Missing path parameter: contentId');
        self::assertSame('path', $contentIdParameter['in']);
        self::assertTrue($contentIdParameter['required']);
        self::assertSame('string', $contentIdParameter['schema']['type']);
        self::assertSame('uuid', $contentIdParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $contentIdParameter['example']);

        self::assertNotNull($artifactIdParameter, 'Missing path parameter: artifactId');
        self::assertSame('path', $artifactIdParameter['in']);
        self::assertTrue($artifactIdParameter['required']);
        self::assertSame('string', $artifactIdParameter['schema']['type']);
        self::assertSame('uuid', $artifactIdParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440002', $artifactIdParameter['example']);
    }

    public function testOpenApiSpecDocumentsGetArtifactRecommendationsResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/contents/{contentId}/artifacts/{artifactId}/recommendations']['get']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Artifact recommendations projection', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/ArtifactRecommendations',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsRecommendationSchemas(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('RecommendedArtifact', $spec['components']['schemas']);
        self::assertArrayHasKey('ArtifactRecommendations', $spec['components']['schemas']);
        self::assertArrayHasKey('RecommendationReason', $spec['components']['schemas']);

        $recommendedSchema = $spec['components']['schemas']['RecommendedArtifact'];
        $recommendationsSchema = $spec['components']['schemas']['ArtifactRecommendations'];
        $reasonSchema = $spec['components']['schemas']['RecommendationReason'];

        self::assertArrayHasKey('artifactId', $recommendedSchema['properties']);
        self::assertArrayHasKey('type', $recommendedSchema['properties']);
        self::assertArrayHasKey('title', $recommendedSchema['properties']);
        self::assertArrayHasKey('reason', $recommendedSchema['properties']);
        self::assertArrayHasKey('score', $recommendedSchema['properties']);
        self::assertSame(
            '#/components/schemas/ArtifactType',
            $recommendedSchema['properties']['type']['$ref'],
        );
        self::assertSame(
            '#/components/schemas/RecommendationReason',
            $recommendedSchema['properties']['reason']['$ref'],
        );
        self::assertSame('integer', $recommendedSchema['properties']['score']['type']);
        self::assertSame(0, $recommendedSchema['properties']['score']['minimum']);
        self::assertSame(100, $recommendedSchema['properties']['score']['maximum']);
        self::assertSame(80, $recommendedSchema['properties']['score']['example']);
        if (isset($recommendedSchema['required'])) {
            self::assertContains('artifactId', $recommendedSchema['required']);
            self::assertContains('type', $recommendedSchema['required']);
            self::assertContains('title', $recommendedSchema['required']);
            self::assertContains('reason', $recommendedSchema['required']);
            self::assertContains('score', $recommendedSchema['required']);
        }

        self::assertArrayHasKey('recommendations', $recommendationsSchema['properties']);
        self::assertSame('array', $recommendationsSchema['properties']['recommendations']['type']);
        self::assertSame(
            '#/components/schemas/RecommendedArtifact',
            $recommendationsSchema['properties']['recommendations']['items']['$ref'],
        );
        if (isset($recommendationsSchema['required'])) {
            self::assertContains('recommendations', $recommendationsSchema['required']);
        }

        self::assertSame('string', $reasonSchema['type']);
        self::assertSame(
            ['related', 'derived_from', 'references', 'next', 'previous'],
            $reasonSchema['enum'],
        );
        self::assertSame('derived_from', $reasonSchema['example']);
    }

    public function testOpenApiSpecDocumentsSearchSemanticChunksPathParameters(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/semantic-search']['get'];

        self::assertSame('searchSemanticChunks', $operation['operationId']);

        $contentIdParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'contentId') {
                $contentIdParameter = $parameter;
            }
        }

        self::assertNotNull($contentIdParameter, 'Missing path parameter: contentId');
        self::assertSame('path', $contentIdParameter['in']);
        self::assertTrue($contentIdParameter['required']);
        self::assertSame('string', $contentIdParameter['schema']['type']);
        self::assertSame('uuid', $contentIdParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $contentIdParameter['example']);
    }

    public function testOpenApiSpecDocumentsSearchSemanticChunksQueryParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/semantic-search']['get'];

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
        self::assertSame(500, $queryParameter['schema']['maxLength']);
        self::assertSame('rome', $queryParameter['schema']['example']);
    }

    public function testOpenApiSpecDocumentsSearchSemanticChunksResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/contents/{contentId}/semantic-search']['get']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Semantic search results', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/SemanticSearchResult',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsSemanticSearchSchemas(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('RetrievedChunk', $spec['components']['schemas']);
        self::assertArrayHasKey('SemanticSearchResult', $spec['components']['schemas']);

        $chunkSchema = $spec['components']['schemas']['RetrievedChunk'];
        $resultSchema = $spec['components']['schemas']['SemanticSearchResult'];

        self::assertArrayHasKey('artifactId', $chunkSchema['properties']);
        self::assertArrayHasKey('chunkId', $chunkSchema['properties']);
        self::assertArrayHasKey('position', $chunkSchema['properties']);
        self::assertArrayHasKey('text', $chunkSchema['properties']);
        self::assertArrayHasKey('score', $chunkSchema['properties']);
        self::assertSame('number', $chunkSchema['properties']['score']['type']);
        self::assertSame(0, $chunkSchema['properties']['score']['minimum']);
        self::assertSame(1, $chunkSchema['properties']['score']['maximum']);
        self::assertSame(0.87, $chunkSchema['properties']['score']['example']);
        if (isset($chunkSchema['required'])) {
            self::assertContains('artifactId', $chunkSchema['required']);
            self::assertContains('chunkId', $chunkSchema['required']);
            self::assertContains('position', $chunkSchema['required']);
            self::assertContains('text', $chunkSchema['required']);
            self::assertContains('score', $chunkSchema['required']);
        }

        self::assertArrayHasKey('results', $resultSchema['properties']);
        self::assertSame('array', $resultSchema['properties']['results']['type']);
        self::assertSame(
            '#/components/schemas/RetrievedChunk',
            $resultSchema['properties']['results']['items']['$ref'],
        );
        if (isset($resultSchema['required'])) {
            self::assertContains('results', $resultSchema['required']);
        }
    }

    public function testOpenApiSpecDocumentsAskContentChatPathParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/chat']['post'];

        self::assertSame('askContentChat', $operation['operationId']);

        $pathParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'contentId') {
                $pathParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($pathParameter, 'Missing path parameter: contentId');
        self::assertSame('path', $pathParameter['in']);
        self::assertTrue($pathParameter['required']);
        self::assertSame('string', $pathParameter['schema']['type']);
        self::assertSame('uuid', $pathParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $pathParameter['example']);
    }

    public function testOpenApiSpecDocumentsAskContentChatRequestBody(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $requestBody = $spec['paths']['/api/contents/{contentId}/chat']['post']['requestBody'];

        self::assertTrue($requestBody['required']);
        self::assertSame(
            '#/components/schemas/ChatRequest',
            $requestBody['content']['application/json']['schema']['$ref'],
        );

        $questionProperty = $spec['components']['schemas']['ChatRequest']['properties']['question'];

        self::assertSame('string', $questionProperty['type']);
        self::assertSame(1, $questionProperty['minLength']);
        self::assertSame(2000, $questionProperty['maxLength']);
        self::assertSame('Why did Rome collapse?', $questionProperty['example']);
    }

    public function testOpenApiSpecDocumentsAskContentChatResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/contents/{contentId}/chat']['post']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Chat answer with sources', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/ChatAnswer',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsChatSchemas(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('ChatRequest', $spec['components']['schemas']);
        self::assertArrayHasKey('ChatAnswer', $spec['components']['schemas']);
        self::assertArrayHasKey('ChatSource', $spec['components']['schemas']);
        self::assertArrayHasKey('ChatCitation', $spec['components']['schemas']);

        $answerSchema = $spec['components']['schemas']['ChatAnswer'];
        $sourceSchema = $spec['components']['schemas']['ChatSource'];
        $citationSchema = $spec['components']['schemas']['ChatCitation'];

        self::assertArrayHasKey('answer', $answerSchema['properties']);
        self::assertArrayHasKey('sources', $answerSchema['properties']);
        self::assertArrayHasKey('citations', $answerSchema['properties']);
        self::assertSame('string', $answerSchema['properties']['answer']['type']);
        self::assertSame(
            'Mock answer based on retrieved context [1].',
            $answerSchema['properties']['answer']['example'],
        );
        self::assertSame('array', $answerSchema['properties']['sources']['type']);
        self::assertSame(
            '#/components/schemas/ChatSource',
            $answerSchema['properties']['sources']['items']['$ref'],
        );
        self::assertSame('array', $answerSchema['properties']['citations']['type']);
        self::assertSame(
            '#/components/schemas/ChatCitation',
            $answerSchema['properties']['citations']['items']['$ref'],
        );
        if (isset($answerSchema['required'])) {
            self::assertContains('answer', $answerSchema['required']);
            self::assertContains('sources', $answerSchema['required']);
            self::assertContains('citations', $answerSchema['required']);
        }

        self::assertArrayHasKey('artifactId', $sourceSchema['properties']);
        self::assertArrayHasKey('chunkId', $sourceSchema['properties']);
        self::assertArrayHasKey('text', $sourceSchema['properties']);
        self::assertArrayHasKey('score', $sourceSchema['properties']);
        self::assertArrayNotHasKey('position', $sourceSchema['properties']);
        self::assertSame('number', $sourceSchema['properties']['score']['type']);
        self::assertSame(0, $sourceSchema['properties']['score']['minimum']);
        self::assertSame(1, $sourceSchema['properties']['score']['maximum']);
        self::assertSame(0.87, $sourceSchema['properties']['score']['example']);
        if (isset($sourceSchema['required'])) {
            self::assertContains('artifactId', $sourceSchema['required']);
            self::assertContains('chunkId', $sourceSchema['required']);
            self::assertContains('text', $sourceSchema['required']);
            self::assertContains('score', $sourceSchema['required']);
        }

        self::assertArrayHasKey('number', $citationSchema['properties']);
        self::assertArrayHasKey('artifactId', $citationSchema['properties']);
        self::assertArrayHasKey('chunkId', $citationSchema['properties']);
        self::assertArrayHasKey('score', $citationSchema['properties']);
        self::assertArrayNotHasKey('text', $citationSchema['properties']);
        self::assertSame('integer', $citationSchema['properties']['number']['type']);
        self::assertSame(1, $citationSchema['properties']['number']['minimum']);
        self::assertSame('number', $citationSchema['properties']['score']['type']);
        self::assertSame(0, $citationSchema['properties']['score']['minimum']);
        self::assertSame(1, $citationSchema['properties']['score']['maximum']);
        self::assertSame(0.87, $citationSchema['properties']['score']['example']);
        if (isset($citationSchema['required'])) {
            self::assertContains('number', $citationSchema['required']);
            self::assertContains('artifactId', $citationSchema['required']);
            self::assertContains('chunkId', $citationSchema['required']);
            self::assertContains('score', $citationSchema['required']);
        }
    }

    public function testOpenApiSpecDocumentsAskContentChatStreamPathParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/chat/stream']['post'];

        self::assertSame('askContentChatStream', $operation['operationId']);

        $pathParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'contentId') {
                $pathParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($pathParameter, 'Missing path parameter: contentId');
        self::assertSame('path', $pathParameter['in']);
        self::assertTrue($pathParameter['required']);
        self::assertSame('string', $pathParameter['schema']['type']);
        self::assertSame('uuid', $pathParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $pathParameter['example']);
    }

    public function testOpenApiSpecDocumentsAskContentChatStreamRequestBody(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $requestBody = $spec['paths']['/api/contents/{contentId}/chat/stream']['post']['requestBody'];

        self::assertTrue($requestBody['required']);
        self::assertSame(
            '#/components/schemas/ChatRequest',
            $requestBody['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsAskContentChatStreamResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/contents/{contentId}/chat/stream']['post']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame(
            'SSE stream of chat tokens followed by a done event',
            $responses['200']['description'],
        );
        self::assertArrayHasKey('text/event-stream', $responses['200']['content']);
        self::assertSame(
            'string',
            $responses['200']['content']['text/event-stream']['schema']['type'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsChatStreamTokenSchema(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('ChatStreamToken', $spec['components']['schemas']);

        $tokenSchema = $spec['components']['schemas']['ChatStreamToken'];

        self::assertArrayHasKey('index', $tokenSchema['properties']);
        self::assertArrayHasKey('text', $tokenSchema['properties']);
        self::assertSame('integer', $tokenSchema['properties']['index']['type']);
        self::assertSame(0, $tokenSchema['properties']['index']['minimum']);
        self::assertSame('string', $tokenSchema['properties']['text']['type']);
        self::assertSame(1, $tokenSchema['properties']['text']['minLength']);
        self::assertSame('Mock ', $tokenSchema['properties']['text']['example']);
        if (isset($tokenSchema['required'])) {
            self::assertContains('index', $tokenSchema['required']);
            self::assertContains('text', $tokenSchema['required']);
        }
    }

    public function testOpenApiSpecDocumentsAskConversationChatPathParameters(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/conversations/{conversationId}/chat']['post'];

        self::assertSame('askConversationChat', $operation['operationId']);

        $contentIdParameter = null;
        $conversationIdParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'contentId') {
                $contentIdParameter = $parameter;
            }

            if (($parameter['name'] ?? null) === 'conversationId') {
                $conversationIdParameter = $parameter;
            }
        }

        self::assertNotNull($contentIdParameter, 'Missing path parameter: contentId');
        self::assertSame('path', $contentIdParameter['in']);
        self::assertTrue($contentIdParameter['required']);
        self::assertSame('string', $contentIdParameter['schema']['type']);
        self::assertSame('uuid', $contentIdParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $contentIdParameter['example']);

        self::assertNotNull($conversationIdParameter, 'Missing path parameter: conversationId');
        self::assertSame('path', $conversationIdParameter['in']);
        self::assertTrue($conversationIdParameter['required']);
        self::assertSame('string', $conversationIdParameter['schema']['type']);
        self::assertSame('uuid', $conversationIdParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440001', $conversationIdParameter['example']);
    }

    public function testOpenApiSpecDocumentsAskConversationChatRequestBody(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $requestBody = $spec['paths']['/api/contents/{contentId}/conversations/{conversationId}/chat']['post']['requestBody'];

        self::assertTrue($requestBody['required']);
        self::assertSame(
            '#/components/schemas/ChatRequest',
            $requestBody['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsAskConversationChatResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/contents/{contentId}/conversations/{conversationId}/chat']['post']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Updated conversation with chat answer', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/ConversationChatResponse',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsConversationSchemas(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('Conversation', $spec['components']['schemas']);
        self::assertArrayHasKey('ConversationMessage', $spec['components']['schemas']);
        self::assertArrayHasKey('ConversationChatResponse', $spec['components']['schemas']);

        $conversationSchema = $spec['components']['schemas']['Conversation'];
        $messageSchema = $spec['components']['schemas']['ConversationMessage'];
        $responseSchema = $spec['components']['schemas']['ConversationChatResponse'];

        self::assertSame('string', $conversationSchema['properties']['id']['type']);
        self::assertSame('uuid', $conversationSchema['properties']['id']['format']);
        self::assertSame('string', $conversationSchema['properties']['contentId']['type']);
        self::assertSame('uuid', $conversationSchema['properties']['contentId']['format']);
        self::assertSame('array', $conversationSchema['properties']['messages']['type']);
        self::assertSame(
            '#/components/schemas/ConversationMessage',
            $conversationSchema['properties']['messages']['items']['$ref'],
        );
        self::assertSame('array', $conversationSchema['properties']['documents']['type']);
        self::assertSame(
            '#/components/schemas/SelectedDocument',
            $conversationSchema['properties']['documents']['items']['$ref'],
        );
        if (isset($conversationSchema['required'])) {
            self::assertContains('documents', $conversationSchema['required']);
        }

        self::assertSame('string', $messageSchema['properties']['role']['type']);
        self::assertContains('user', $messageSchema['properties']['role']['enum']);
        self::assertContains('assistant', $messageSchema['properties']['role']['enum']);
        self::assertSame('string', $messageSchema['properties']['text']['type']);

        self::assertSame(
            '#/components/schemas/Conversation',
            $responseSchema['properties']['conversation']['$ref'],
        );
        self::assertSame(
            '#/components/schemas/ChatAnswer',
            $responseSchema['properties']['answer']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsAskConversationChatStreamPathParameters(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/conversations/{conversationId}/chat/stream']['post'];

        self::assertSame('askConversationChatStream', $operation['operationId']);

        $contentIdParameter = null;
        $conversationIdParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'contentId') {
                $contentIdParameter = $parameter;
            }

            if (($parameter['name'] ?? null) === 'conversationId') {
                $conversationIdParameter = $parameter;
            }
        }

        self::assertNotNull($contentIdParameter, 'Missing path parameter: contentId');
        self::assertSame('path', $contentIdParameter['in']);
        self::assertTrue($contentIdParameter['required']);
        self::assertSame('string', $contentIdParameter['schema']['type']);
        self::assertSame('uuid', $contentIdParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $contentIdParameter['example']);

        self::assertNotNull($conversationIdParameter, 'Missing path parameter: conversationId');
        self::assertSame('path', $conversationIdParameter['in']);
        self::assertTrue($conversationIdParameter['required']);
        self::assertSame('string', $conversationIdParameter['schema']['type']);
        self::assertSame('uuid', $conversationIdParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440001', $conversationIdParameter['example']);
    }

    public function testOpenApiSpecDocumentsAskConversationChatStreamRequestBody(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $requestBody = $spec['paths']['/api/contents/{contentId}/conversations/{conversationId}/chat/stream']['post']['requestBody'];

        self::assertTrue($requestBody['required']);
        self::assertSame(
            '#/components/schemas/ChatRequest',
            $requestBody['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsAskConversationChatStreamResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/contents/{contentId}/conversations/{conversationId}/chat/stream']['post']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame(
            'SSE stream of chat tokens, persisted conversation, and done event',
            $responses['200']['description'],
        );
        self::assertArrayHasKey('text/event-stream', $responses['200']['content']);
        self::assertSame(
            'string',
            $responses['200']['content']['text/event-stream']['schema']['type'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsConversationStreamEventSchema(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('ConversationStreamEvent', $spec['components']['schemas']);

        $eventSchema = $spec['components']['schemas']['ConversationStreamEvent'];

        self::assertSame(
            '#/components/schemas/Conversation',
            $eventSchema['properties']['conversation']['$ref'],
        );
        if (isset($eventSchema['required'])) {
            self::assertContains('conversation', $eventSchema['required']);
        }
    }

    public function testOpenApiSpecDocumentsUpdateConversationDocumentsPathParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/conversations/{conversationId}/documents']['put'];

        self::assertSame('updateConversationDocuments', $operation['operationId']);

        $pathParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'conversationId') {
                $pathParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($pathParameter, 'Missing path parameter: conversationId');
        self::assertSame('path', $pathParameter['in']);
        self::assertTrue($pathParameter['required']);
        self::assertSame('string', $pathParameter['schema']['type']);
        self::assertSame('uuid', $pathParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440001', $pathParameter['example']);
    }

    public function testOpenApiSpecDocumentsUpdateConversationDocumentsRequestBody(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $requestBody = $spec['paths']['/api/conversations/{conversationId}/documents']['put']['requestBody'];

        self::assertTrue($requestBody['required']);
        self::assertSame(
            '#/components/schemas/UpdateConversationDocumentsRequest',
            $requestBody['content']['application/json']['schema']['$ref'],
        );

        $contentIdsProperty = $spec['components']['schemas']['UpdateConversationDocumentsRequest']['properties']['contentIds'];

        self::assertSame('array', $contentIdsProperty['type']);
        self::assertSame(1, $contentIdsProperty['minItems']);
        self::assertSame('string', $contentIdsProperty['items']['type']);
        self::assertSame('uuid', $contentIdsProperty['items']['format']);
    }

    public function testOpenApiSpecDocumentsUpdateConversationDocumentsResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/conversations/{conversationId}/documents']['put']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Updated conversation', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/ConversationResponse',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('404', $responses);
        self::assertSame('Conversation not found', $responses['404']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['404']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsSelectedDocumentSchema(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('SelectedDocument', $spec['components']['schemas']);
        self::assertArrayHasKey('ConversationResponse', $spec['components']['schemas']);

        $selectedDocumentSchema = $spec['components']['schemas']['SelectedDocument'];
        $conversationResponseSchema = $spec['components']['schemas']['ConversationResponse'];

        self::assertSame('string', $selectedDocumentSchema['properties']['contentId']['type']);
        self::assertSame('uuid', $selectedDocumentSchema['properties']['contentId']['format']);
        if (isset($selectedDocumentSchema['required'])) {
            self::assertContains('contentId', $selectedDocumentSchema['required']);
        }

        self::assertSame(
            '#/components/schemas/Conversation',
            $conversationResponseSchema['properties']['conversation']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsGetPlatformMetricsOperation(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/internal/platform/metrics']['get'];

        self::assertSame('getPlatformMetrics', $operation['operationId']);
        self::assertContains('Platform', $operation['tags']);
    }

    public function testOpenApiSpecDocumentsGetPlatformMetricsQueryParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/internal/platform/metrics']['get'];

        $queryParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'limit') {
                $queryParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($queryParameter, 'Missing query parameter: limit');
        self::assertSame('query', $queryParameter['in']);
        self::assertFalse($queryParameter['required']);
        self::assertSame('integer', $queryParameter['schema']['type']);
        self::assertSame(1, $queryParameter['schema']['minimum']);
        self::assertSame(100, $queryParameter['schema']['maximum']);
        self::assertSame(20, $queryParameter['schema']['example']);
    }

    public function testOpenApiSpecDocumentsGetPlatformMetricsResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/internal/platform/metrics']['get']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame(
            'Recent performance metric snapshots (newest first)',
            $responses['200']['description'],
        );
        self::assertSame(
            '#/components/schemas/PlatformMetricsResponse',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid limit', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsPlatformMetricsSchemas(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('PerformanceMetric', $spec['components']['schemas']);
        self::assertArrayHasKey('PerformanceMetricSnapshot', $spec['components']['schemas']);
        self::assertArrayHasKey('PlatformMetricsResponse', $spec['components']['schemas']);

        $metricSchema = $spec['components']['schemas']['PerformanceMetric'];
        $snapshotSchema = $spec['components']['schemas']['PerformanceMetricSnapshot'];
        $responseSchema = $spec['components']['schemas']['PlatformMetricsResponse'];

        self::assertArrayHasKey('name', $metricSchema['properties']);
        self::assertArrayHasKey('durationMs', $metricSchema['properties']);
        self::assertSame('string', $metricSchema['properties']['name']['type']);
        self::assertSame('integer', $metricSchema['properties']['durationMs']['type']);
        self::assertSame(0, $metricSchema['properties']['durationMs']['minimum']);
        if (isset($metricSchema['required'])) {
            self::assertContains('name', $metricSchema['required']);
            self::assertContains('durationMs', $metricSchema['required']);
        }

        self::assertArrayHasKey('correlationId', $snapshotSchema['properties']);
        self::assertArrayHasKey('recordedAt', $snapshotSchema['properties']);
        self::assertArrayHasKey('metrics', $snapshotSchema['properties']);
        self::assertSame('string', $snapshotSchema['properties']['correlationId']['type']);
        self::assertSame('uuid', $snapshotSchema['properties']['correlationId']['format']);
        self::assertSame('string', $snapshotSchema['properties']['recordedAt']['type']);
        self::assertSame('date-time', $snapshotSchema['properties']['recordedAt']['format']);
        self::assertSame('array', $snapshotSchema['properties']['metrics']['type']);
        self::assertSame(
            '#/components/schemas/PerformanceMetric',
            $snapshotSchema['properties']['metrics']['items']['$ref'],
        );
        if (isset($snapshotSchema['required'])) {
            self::assertContains('correlationId', $snapshotSchema['required']);
            self::assertContains('recordedAt', $snapshotSchema['required']);
            self::assertContains('metrics', $snapshotSchema['required']);
        }

        self::assertArrayHasKey('snapshots', $responseSchema['properties']);
        self::assertSame('array', $responseSchema['properties']['snapshots']['type']);
        self::assertSame(
            '#/components/schemas/PerformanceMetricSnapshot',
            $responseSchema['properties']['snapshots']['items']['$ref'],
        );
        if (isset($responseSchema['required'])) {
            self::assertContains('snapshots', $responseSchema['required']);
        }
    }

    public function testOpenApiSpecDocumentsRunContentAgentPathParameter(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $operation = $spec['paths']['/api/contents/{contentId}/agent/run']['post'];

        self::assertSame('runContentAgent', $operation['operationId']);

        $pathParameter = null;

        foreach ($operation['parameters'] as $parameter) {
            if (($parameter['name'] ?? null) === 'contentId') {
                $pathParameter = $parameter;
                break;
            }
        }

        self::assertNotNull($pathParameter, 'Missing path parameter: contentId');
        self::assertSame('path', $pathParameter['in']);
        self::assertTrue($pathParameter['required']);
        self::assertSame('string', $pathParameter['schema']['type']);
        self::assertSame('uuid', $pathParameter['schema']['format']);
        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $pathParameter['example']);
    }

    public function testOpenApiSpecDocumentsRunContentAgentRequestBody(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $requestBody = $spec['paths']['/api/contents/{contentId}/agent/run']['post']['requestBody'];

        self::assertTrue($requestBody['required']);
        self::assertSame(
            '#/components/schemas/AgentRunRequest',
            $requestBody['content']['application/json']['schema']['$ref'],
        );

        $requestSchema = $spec['components']['schemas']['AgentRunRequest'];
        $questionProperty = $requestSchema['properties']['question'];
        $conversationIdProperty = $requestSchema['properties']['conversationId'];

        self::assertSame('string', $questionProperty['type']);
        self::assertSame(1, $questionProperty['minLength']);
        self::assertSame(2000, $questionProperty['maxLength']);
        self::assertSame('Compare Rome and Byzantium', $questionProperty['example']);
        self::assertContains('string', (array) $conversationIdProperty['type']);
        self::assertSame('uuid', $conversationIdProperty['format']);
        self::assertContains('question', $requestSchema['required']);
        self::assertNotContains('conversationId', $requestSchema['required'] ?? []);
    }

    public function testOpenApiSpecDocumentsRunContentAgentResponses(): void
    {
        $spec = $this->fetchOpenApiSpec();
        $responses = $spec['paths']['/api/contents/{contentId}/agent/run']['post']['responses'];

        self::assertArrayHasKey('200', $responses);
        self::assertSame('Agent plan and execution trace', $responses['200']['description']);
        self::assertSame(
            '#/components/schemas/AgentExecution',
            $responses['200']['content']['application/json']['schema']['$ref'],
        );

        self::assertArrayHasKey('400', $responses);
        self::assertSame('Invalid request', $responses['400']['description']);
        self::assertSame(
            '#/components/schemas/ErrorResponse',
            $responses['400']['content']['application/json']['schema']['$ref'],
        );
    }

    public function testOpenApiSpecDocumentsAgentSchemas(): void
    {
        $spec = $this->fetchOpenApiSpec();

        self::assertArrayHasKey('AgentRunRequest', $spec['components']['schemas']);
        self::assertArrayHasKey('AgentExecution', $spec['components']['schemas']);
        self::assertArrayHasKey('AgentPlanStep', $spec['components']['schemas']);
        self::assertArrayHasKey('AgentExecutionStep', $spec['components']['schemas']);
        self::assertArrayHasKey('AgentTool', $spec['components']['schemas']);
        self::assertArrayHasKey('AgentExecutionStatus', $spec['components']['schemas']);

        $executionSchema = $spec['components']['schemas']['AgentExecution'];
        $planStepSchema = $spec['components']['schemas']['AgentPlanStep'];
        $executionStepSchema = $spec['components']['schemas']['AgentExecutionStep'];
        $toolSchema = $spec['components']['schemas']['AgentTool'];
        $statusSchema = $spec['components']['schemas']['AgentExecutionStatus'];

        self::assertArrayHasKey('plan', $executionSchema['properties']);
        self::assertArrayHasKey('steps', $executionSchema['properties']);
        self::assertArrayHasKey('finalSummary', $executionSchema['properties']);
        self::assertArrayHasKey('metadata', $executionSchema['properties']);
        self::assertSame('array', $executionSchema['properties']['plan']['type']);
        self::assertSame('array', $executionSchema['properties']['steps']['type']);
        self::assertSame('object', $executionSchema['properties']['metadata']['type']);
        self::assertTrue($executionSchema['properties']['metadata']['additionalProperties'] ?? false);
        self::assertArrayHasKey('example', $executionSchema['properties']['metadata']);
        self::assertSame(3, $executionSchema['properties']['metadata']['example']['resultCount']);
        self::assertSame(12, $executionSchema['properties']['metadata']['example']['nodeCount']);
        self::assertSame(
            '#/components/schemas/AgentPlanStep',
            $executionSchema['properties']['plan']['items']['$ref'],
        );
        self::assertSame(
            '#/components/schemas/AgentExecutionStep',
            $executionSchema['properties']['steps']['items']['$ref'],
        );
        self::assertSame('string', $executionSchema['properties']['finalSummary']['type']);
        self::assertSame('Agent workflow completed.', $executionSchema['properties']['finalSummary']['example']);
        if (isset($executionSchema['required'])) {
            self::assertContains('plan', $executionSchema['required']);
            self::assertContains('steps', $executionSchema['required']);
            self::assertContains('finalSummary', $executionSchema['required']);
            self::assertContains('metadata', $executionSchema['required']);
        }

        self::assertSame(
            '#/components/schemas/AgentTool',
            $planStepSchema['properties']['tool']['$ref'],
        );
        self::assertSame('string', $planStepSchema['properties']['description']['type']);
        if (isset($planStepSchema['required'])) {
            self::assertContains('order', $planStepSchema['required']);
            self::assertContains('tool', $planStepSchema['required']);
            self::assertContains('description', $planStepSchema['required']);
        }

        self::assertSame(
            '#/components/schemas/AgentTool',
            $executionStepSchema['properties']['tool']['$ref'],
        );
        self::assertSame(
            '#/components/schemas/AgentExecutionStatus',
            $executionStepSchema['properties']['status']['$ref'],
        );
        self::assertSame('string', $executionStepSchema['properties']['summary']['type']);
        self::assertArrayHasKey('metadata', $executionStepSchema['properties']);
        self::assertSame('object', $executionStepSchema['properties']['metadata']['type']);
        self::assertTrue($executionStepSchema['properties']['metadata']['additionalProperties'] ?? false);
        self::assertArrayHasKey('example', $executionStepSchema['properties']['metadata']);
        self::assertSame(3, $executionStepSchema['properties']['metadata']['example']['resultCount']);
        self::assertSame(0.91, $executionStepSchema['properties']['metadata']['example']['topScore']);
        if (isset($executionStepSchema['required'])) {
            self::assertContains('order', $executionStepSchema['required']);
            self::assertContains('tool', $executionStepSchema['required']);
            self::assertContains('status', $executionStepSchema['required']);
            self::assertContains('summary', $executionStepSchema['required']);
            self::assertContains('metadata', $executionStepSchema['required']);
        }

        self::assertSame('string', $toolSchema['type']);
        self::assertSame(
            ['semantic_search', 'knowledge_graph', 'conversation_memory', 'multi_document_chat'],
            $toolSchema['enum'],
        );
        self::assertSame('semantic_search', $toolSchema['example']);

        self::assertSame('string', $statusSchema['type']);
        self::assertSame(['completed', 'skipped', 'failed'], $statusSchema['enum']);
        self::assertSame('completed', $statusSchema['example']);
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
