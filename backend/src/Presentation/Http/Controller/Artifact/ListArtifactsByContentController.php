<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Artifact;

use App\Application\Artifact\Handlers\ListArtifactsByContentHandler;
use App\Application\Artifact\Queries\ListArtifactsByContentQuery;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Response\Artifact\ListArtifactsResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListArtifactsByContentController extends AbstractController
{
    #[Route(
        '/api/contents/{contentId}/artifacts',
        name: 'api_contents_artifacts_list',
        methods: ['GET'],
    )]
    public function __invoke(
        string $contentId,
        ListArtifactsByContentHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new ListArtifactsByContentQuery($contentId));

        return $this->json(ListArtifactsResponse::fromResult($result));
    }
}
