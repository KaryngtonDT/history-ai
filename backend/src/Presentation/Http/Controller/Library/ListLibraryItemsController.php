<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Library;

use App\Application\Library\Handlers\ListLibraryItemsHandler;
use App\Application\Library\Queries\ListLibraryItemsQuery;
use App\Presentation\Http\Response\Library\ListLibraryItemsResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListLibraryItemsController extends AbstractController
{
    #[Route('/api/library/items', name: 'api_library_items_list', methods: ['GET'])]
    public function __invoke(ListLibraryItemsHandler $handler): JsonResponse
    {
        $result = $handler(new ListLibraryItemsQuery());

        return $this->json(ListLibraryItemsResponse::fromResult($result));
    }
}
