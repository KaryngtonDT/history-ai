<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Timeline;

use App\Application\Timeline\Handlers\GetTimelineHandler;
use App\Application\Timeline\Queries\GetTimelineQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\Exception\InvalidArtifactException;
use App\Presentation\Http\Response\Timeline\TimelineResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetTimelineController extends AbstractController
{
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
