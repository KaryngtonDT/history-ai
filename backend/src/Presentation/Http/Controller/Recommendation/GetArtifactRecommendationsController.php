<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Recommendation;

use App\Application\Recommendation\Handlers\GetArtifactRecommendationsHandler;
use App\Application\Recommendation\Queries\GetArtifactRecommendationsQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Response\Recommendation\ArtifactRecommendationsResponse;
use App\Presentation\OpenApi\Schema\ArtifactRecommendations;
use App\Presentation\OpenApi\Schema\RecommendedArtifact;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetArtifactRecommendationsController extends AbstractController
{
    #[OA\Get(
        operationId: 'getArtifactRecommendations',
        summary: 'Get artifact recommendations for content',
        description: 'Returns contextual artifact recommendations derived from the knowledge graph for a specific artifact within a content resource.',
        tags: ['Recommendations'],
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
                description: 'Artifact recommendations projection',
                content: new OA\JsonContent(ref: '#/components/schemas/ArtifactRecommendations'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/contents/{contentId}/artifacts/{artifactId}/recommendations',
        name: 'api_contents_artifact_recommendations_get',
        methods: ['GET'],
    )]
    public function __invoke(
        string $contentId,
        string $artifactId,
        GetArtifactRecommendationsHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
            new ArtifactId($artifactId);
        } catch (InvalidContentIdException|InvalidArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new GetArtifactRecommendationsQuery($contentId, $artifactId));

        return $this->json(ArtifactRecommendationsResponse::fromResult($result));
    }
}
