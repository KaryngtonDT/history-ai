<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Project;

use App\Application\Workspace\Handlers\GetProjectHandler;
use App\Application\Workspace\Queries\GetProjectQuery;
use App\Domain\Workspace\Exception\InvalidProjectException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetProjectController extends AbstractController
{
    #[OA\Get(
        operationId: 'getProject',
        summary: 'Get a workspace project',
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project details', content: new OA\JsonContent(ref: '#/components/schemas/Project')),
            new OA\Response(response: 404, description: 'Project not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/projects/{id}', name: 'api_projects_get', methods: ['GET'])]
    public function __invoke(string $id, GetProjectHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetProjectQuery($id));
        } catch (InvalidProjectException) {
            return $this->json(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(ProjectResponseFactory::fromResult($result));
    }
}
