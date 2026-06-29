<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Graph;

use App\Application\Graph\Handlers\GetGraphNeighborhoodHandler;
use App\Application\Graph\Queries\GetGraphNeighborhoodQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Response\Graph\GraphNeighborhoodResponse;
use App\Presentation\OpenApi\Schema\GraphNeighborhood;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetGraphNeighborhoodController extends AbstractController
{
    #[OA\Get(
        operationId: 'getGraphNeighborhood',
        summary: 'Get direct neighborhood of an artifact in a content graph',
        description: 'Returns the center node, direct neighbor nodes, and connecting edges for an artifact within the knowledge graph projection of a content resource.',
        tags: ['Graph'],
        parameters: [
            new OA\Parameter(
                name: 'contentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440000',
            ),
            new OA\Parameter(
                name: 'artifactId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440002',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Direct artifact neighborhood',
                content: new OA\JsonContent(ref: '#/components/schemas/GraphNeighborhood'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Artifact not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/contents/{contentId}/graph/artifacts/{artifactId}/neighborhood',
        name: 'api_contents_graph_artifact_neighborhood_get',
        methods: ['GET'],
    )]
    public function __invoke(
        string $contentId,
        string $artifactId,
        GetGraphNeighborhoodHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
            new ArtifactId($artifactId);
        } catch (InvalidContentIdException|InvalidArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new GetGraphNeighborhoodQuery($contentId, $artifactId));

        if (null === $result) {
            return $this->json(
                ['error' => 'Artifact not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json(GraphNeighborhoodResponse::fromResult($result));
    }
}
