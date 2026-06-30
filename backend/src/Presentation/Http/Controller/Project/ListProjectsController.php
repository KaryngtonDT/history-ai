<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Project;

use App\Application\Workspace\Handlers\ListProjectsHandler;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListProjectsController extends AbstractController
{
    #[OA\Get(
        operationId: 'listProjects',
        summary: 'List workspace projects',
        tags: ['Projects'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Project list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Project'),
                ),
            ),
        ],
    )]
    #[Route('/api/projects', name: 'api_projects_list', methods: ['GET'])]
    public function __invoke(ListProjectsHandler $handler): JsonResponse
    {
        return $this->json(array_map(
            static fn ($result): array => ProjectResponseFactory::fromResult($result),
            $handler(),
        ));
    }
}
