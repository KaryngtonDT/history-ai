<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Graph;

use App\Application\Graph\Handlers\GetKnowledgeGraphHandler;
use App\Application\Graph\Queries\GetKnowledgeGraphQuery;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Presentation\Http\Response\Graph\KnowledgeGraphResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetKnowledgeGraphController extends AbstractController
{
    #[Route(
        '/api/contents/{contentId}/graph',
        name: 'api_contents_graph_get',
        methods: ['GET'],
    )]
    public function __invoke(
        string $contentId,
        GetKnowledgeGraphHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $result = $handler(new GetKnowledgeGraphQuery($contentId));

        return $this->json(KnowledgeGraphResponse::fromResult($result));
    }
}
