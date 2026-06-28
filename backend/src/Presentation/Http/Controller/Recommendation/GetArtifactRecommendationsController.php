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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetArtifactRecommendationsController extends AbstractController
{
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
