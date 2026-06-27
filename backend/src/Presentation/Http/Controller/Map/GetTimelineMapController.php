<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Map;

use App\Application\Map\Handlers\GetTimelineMapHandler;
use App\Application\Map\Queries\GetTimelineMapQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Presentation\Http\Response\Map\TimelineMapResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetTimelineMapController extends AbstractController
{
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
