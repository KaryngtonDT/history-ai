<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Relation;

use App\Application\Relation\Handlers\GetArtifactRelationsHandler;
use App\Application\Relation\Queries\GetArtifactRelationsQuery;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Response\Relation\ArtifactRelationsResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetArtifactRelationsController extends AbstractController
{
    #[Route(
        '/api/contents/{contentId}/relations',
        name: 'api_contents_relations_get',
        methods: ['GET'],
    )]
    public function __invoke(
        string $contentId,
        GetArtifactRelationsHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new GetArtifactRelationsQuery($contentId));

        return $this->json(ArtifactRelationsResponse::fromResult($result));
    }
}
