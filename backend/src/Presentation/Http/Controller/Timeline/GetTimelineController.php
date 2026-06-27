<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Timeline;

use App\Application\Timeline\Handlers\GetTimelineHandler;
use App\Application\Timeline\Queries\GetTimelineQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Presentation\Http\Response\Timeline\TimelineResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetTimelineController extends AbstractController
{
    #[OA\Get(
        operationId: 'getTimeline',
        summary: 'Get structured timeline for an artifact',
        description: 'Returns a parsed timeline projection for a timeline artifact identified by artifact UUID.',
        tags: ['Timeline'],
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
                description: 'Structured timeline',
                content: new OA\JsonContent(ref: '#/components/schemas/Timeline'),
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
        '/api/timeline/{artifactId}',
        name: 'api_timeline_get',
        methods: ['GET'],
    )]
    public function __invoke(string $artifactId, GetTimelineHandler $handler): JsonResponse
    {
        try {
            new ArtifactId($artifactId);
        } catch (InvalidArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new GetTimelineQuery($artifactId));

        if (null === $result) {
            return $this->json(
                ['error' => 'Timeline artifact not found'],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json(TimelineResponse::fromResult($result));
    }
}
