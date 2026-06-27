<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Search;

use App\Application\Search\Handlers\SearchLibraryHandler;
use App\Application\Search\Queries\SearchLibraryQuery;
use App\Presentation\Http\Request\Search\Exception\InvalidSearchRequestException;
use App\Presentation\Http\Request\Search\SearchLibraryRequest;
use App\Presentation\Http\Response\Search\SearchLibraryResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchLibraryController extends AbstractController
{
    #[Route('/api/search/library', name: 'api_search_library', methods: ['GET'])]
    public function __invoke(Request $request, SearchLibraryHandler $handler): JsonResponse
    {
        try {
            $searchRequest = SearchLibraryRequest::fromQueryParameter($request->query->get('q'));
        } catch (InvalidSearchRequestException) {
            return $this->invalidRequestResponse();
        }

        $result = $handler(new SearchLibraryQuery($searchRequest->searchQuery));

        return $this->json(SearchLibraryResponse::fromResult($result));
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
