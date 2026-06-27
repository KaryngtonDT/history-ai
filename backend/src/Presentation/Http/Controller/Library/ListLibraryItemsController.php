<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Library;

use App\Application\Library\Handlers\ListLibraryItemsHandler;
use App\Application\Library\Queries\ListLibraryItemsQuery;
use App\Presentation\Http\Response\Library\ListLibraryItemsResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListLibraryItemsController extends AbstractController
{
    #[OA\Get(
        operationId: 'listLibraryItems',
        summary: 'List library items',
        description: 'Returns all items saved in the user library.',
        tags: ['Library'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Library item list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        required: ['id', 'contentId', 'artifactId', 'type', 'title', 'createdAt'],
                        properties: [
                            new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'contentId', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'artifactId', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'type', type: 'string', example: 'summary'),
                            new OA\Property(property: 'title', type: 'string', example: 'Roman Empire Summary'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        ],
                    ),
                ),
            ),
        ],
    )]
    #[Route('/api/library/items', name: 'api_library_items_list', methods: ['GET'])]
    public function __invoke(ListLibraryItemsHandler $handler): JsonResponse
    {
        $result = $handler(new ListLibraryItemsQuery());

        return $this->json(ListLibraryItemsResponse::fromResult($result));
    }
}
