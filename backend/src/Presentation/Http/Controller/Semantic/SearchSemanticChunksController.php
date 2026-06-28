<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Semantic;

use App\Application\Semantic\Handlers\SearchSemanticChunksHandler;
use App\Application\Semantic\Queries\SearchSemanticChunksQuery;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Semantic\Exception\InvalidSemanticQueryException;
use App\Domain\Semantic\SemanticQuery;
use App\Presentation\Http\Response\Semantic\SemanticSearchResponse;
use App\Presentation\OpenApi\Schema\RetrievedChunk;
use App\Presentation\OpenApi\Schema\SemanticSearchResult;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchSemanticChunksController extends AbstractController
{
    #[OA\Get(
        operationId: 'searchSemanticChunks',
        summary: 'Search semantic chunks for content',
        description: 'Returns artifact chunks ranked by semantic similarity to the query text for a content resource.',
        tags: ['Semantic'],
        parameters: [
            new OA\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440000',
            ),
            new OA\Parameter(
                name: 'q',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 1, maxLength: 500, example: 'rome'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Semantic search results',
                content: new OA\JsonContent(ref: '#/components/schemas/SemanticSearchResult'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/contents/{contentId}/semantic-search',
        name: 'api_contents_semantic_search',
        methods: ['GET'],
    )]
    public function __invoke(
        string $contentId,
        Request $request,
        SearchSemanticChunksHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->invalidRequestResponse();
        }

        $queryParameter = $request->query->get('q');

        if (!is_string($queryParameter)) {
            return $this->invalidRequestResponse();
        }

        try {
            new SemanticQuery($queryParameter);
        } catch (InvalidSemanticQueryException) {
            return $this->invalidRequestResponse();
        }

        $result = $handler(new SearchSemanticChunksQuery($contentId, $queryParameter));

        return $this->json(SemanticSearchResponse::fromResult($result));
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
