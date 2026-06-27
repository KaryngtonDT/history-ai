<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collection;

use App\Application\Collection\Handlers\ListCollectionsHandler;
use App\Application\Collection\Queries\ListCollectionsQuery;
use App\Presentation\Http\Response\Collection\ListCollectionsResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListCollectionsController extends AbstractController
{
    #[OA\Get(
        operationId: 'listCollections',
        summary: 'List collections',
        description: 'Returns all user collections.',
        tags: ['Collections'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Collection list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        required: ['id', 'name', 'description', 'createdAt'],
                        properties: [
                            new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'name', type: 'string', example: 'Ancient Rome'),
                            new OA\Property(property: 'description', type: 'string', nullable: true),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        ],
                    ),
                ),
            ),
        ],
    )]
    #[Route('/api/collections', name: 'api_collections_list', methods: ['GET'])]
    public function __invoke(ListCollectionsHandler $handler): JsonResponse
    {
        $result = $handler(new ListCollectionsQuery());

        return $this->json(ListCollectionsResponse::fromResult($result));
    }
}
