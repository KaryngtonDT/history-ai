<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Semantic;

use App\Application\Semantic\Handlers\SearchSemanticChunksHandler;
use App\Application\Semantic\Queries\SearchSemanticChunksQuery;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Semantic\Exception\InvalidSemanticQueryException;
use App\Domain\Semantic\SemanticQuery;
use App\Presentation\Http\Response\Semantic\SemanticSearchResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchSemanticChunksController extends AbstractController
{
    #[Route(
        '/api/contents/{contentId}/semantic-search',
        name: 'api_contents_semantic_search',
        methods: ['GET'],
    )]
    public function __invoke(
        string $contentId,
        Request $request,
        SearchSemanticChunksHandler $handler,
    ): JsonResponse {
        try {
            new ContentId($contentId);
        } catch (InvalidContentIdException) {
            return $this->invalidRequestResponse();
        }

        $queryParameter = $request->query->get('q');

        if (!is_string($queryParameter)) {
            return $this->invalidRequestResponse();
        }

        try {
            new SemanticQuery($queryParameter);
        } catch (InvalidSemanticQueryException) {
            return $this->invalidRequestResponse();
        }

        $result = $handler(new SearchSemanticChunksQuery($contentId, $queryParameter));

        return $this->json(SemanticSearchResponse::fromResult($result));
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
