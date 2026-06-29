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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetGraphNeighborhoodController extends AbstractController
{
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
