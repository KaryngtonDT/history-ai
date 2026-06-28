<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Map;

use App\Application\Map\Handlers\GetTimelineMapHandler;
use App\Application\Map\Queries\GetTimelineMapQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Presentation\Http\Response\Map\TimelineMapResponse;
use App\Presentation\OpenApi\Schema\Coordinates;
use App\Presentation\OpenApi\Schema\HistoricalPlace;
use App\Presentation\OpenApi\Schema\Map as MapSchema;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetTimelineMapController extends AbstractController
{
    #[OA\Get(
        operationId: 'getTimelineMap',
        summary: 'Get historical map for a timeline artifact',
        description: 'Returns resolved historical places for a timeline artifact identified by artifact UUID.',
        tags: ['Map'],
        parameters: [
            new OA\Parameter(
                name: 'artifactId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
                example: '550e8400-e29b-41d4-a716-446655440000',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Historical map projection',
                content: new OA\JsonContent(ref: '#/components/schemas/Map'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Timeline artifact not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/maps/timeline/{artifactId}',
        name: 'api_maps_timeline_get',
        methods: ['GET'],
    )]
    public function __invoke(string $artifactId, GetTimelineMapHandler $handler): JsonResponse
    {
        try {
            new ArtifactId($artifactId);
        } catch (InvalidArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new GetTimelineMapQuery($artifactId));

        if (null === $result) {
            return $this->json(
                ['error' => 'Timeline artifact not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json(TimelineMapResponse::fromResult($result));
    }
}
