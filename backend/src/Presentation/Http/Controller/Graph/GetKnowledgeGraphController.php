<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Graph;

use App\Application\Graph\Handlers\GetKnowledgeGraphHandler;
use App\Application\Graph\Queries\GetKnowledgeGraphQuery;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Response\Graph\KnowledgeGraphResponse;
use App\Presentation\OpenApi\Schema\GraphEdge;
use App\Presentation\OpenApi\Schema\GraphNode;
use App\Presentation\OpenApi\Schema\KnowledgeGraph;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetKnowledgeGraphController extends AbstractController
{
    #[OA\Get(
        operationId: 'getKnowledgeGraph',
        summary: 'Get knowledge graph for content',
        description: 'Returns artifact nodes and relation edges as a knowledge graph projection for a content resource.',
        tags: ['Graph'],
        parameters: [
            new OA\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440000',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Knowledge graph projection',
                content: new OA\JsonContent(ref: '#/components/schemas/KnowledgeGraph'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/contents/{contentId}/graph',
        name: 'api_contents_graph_get',
        methods: ['GET'],
    )]
    public function __invoke(
        string $contentId,
        GetKnowledgeGraphHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new GetKnowledgeGraphQuery($contentId));

        return $this->json(KnowledgeGraphResponse::fromResult($result));
    }
}
