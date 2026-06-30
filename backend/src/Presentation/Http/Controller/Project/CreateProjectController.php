<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Project;

use App\Application\Workspace\Commands\CreateProjectCommand;
use App\Application\Workspace\DTO\ProjectResult;
use App\Application\Workspace\Handlers\CreateProjectHandler;
use App\Domain\Workspace\Exception\InvalidProjectException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateProjectController extends AbstractController
{
    #[OA\Post(
        operationId: 'createProject',
        summary: 'Create a workspace project',
        tags: ['Projects'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [new OA\Property(property: 'name', type: 'string', example: 'Marketing Campaign')],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Project created', content: new OA\JsonContent(ref: '#/components/schemas/Project')),
            new OA\Response(response: 400, description: 'Invalid request', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/projects', name: 'api_projects_create', methods: ['POST'])]
    public function __invoke(Request $request, CreateProjectHandler $handler): JsonResponse
    {
        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);
        $name = is_array($payload) && is_string($payload['name'] ?? null) ? trim($payload['name']) : '';

        if ('' === $name) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $handler(new CreateProjectCommand($name));
        } catch (InvalidProjectException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(ProjectResponseFactory::fromResult($result), Response::HTTP_CREATED);
    }
}
