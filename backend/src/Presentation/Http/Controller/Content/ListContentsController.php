<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Content;

use App\Application\Content\Handlers\ListContentsHandler;
use App\Application\Content\Queries\ListContentsQuery;
use App\Presentation\Http\Response\Content\ListContentsResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListContentsController extends AbstractController
{
    #[OA\Get(
        operationId: 'listContents',
        summary: 'List contents',
        description: 'Returns all content resources ordered by creation date.',
        tags: ['Contents'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Content list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        required: ['id', 'title', 'sourceType', 'status', 'createdAt', 'updatedAt'],
                        properties: [
                            new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                            new OA\Property(property: 'title', type: 'string', example: 'The Roman Empire'),
                            new OA\Property(property: 'sourceType', type: 'string', example: 'upload_pdf'),
                            new OA\Property(property: 'status', type: 'string', example: 'draft'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
                        ],
                    ),
                ),
            ),
        ],
    )]
    #[Route('/api/contents', name: 'api_contents_list', methods: ['GET'])]
    public function __invoke(ListContentsHandler $handler): JsonResponse
    {
        $result = $handler(new ListContentsQuery());

        return $this->json(ListContentsResponse::fromResult($result));
    }
}
