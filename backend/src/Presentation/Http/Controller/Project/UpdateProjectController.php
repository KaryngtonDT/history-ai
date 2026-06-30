<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Project;

use App\Application\Workspace\Commands\UpdateProjectCommand;
use App\Application\Workspace\Handlers\UpdateProjectHandler;
use App\Domain\Workspace\Exception\InvalidProjectException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateProjectController extends AbstractController
{
    #[Route('/api/projects/{id}', name: 'api_projects_update', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request, UpdateProjectHandler $handler): JsonResponse
    {
        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);
        $name = is_array($payload) && is_string($payload['name'] ?? null) ? trim($payload['name']) : '';

        if ('' === $name) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $handler(new UpdateProjectCommand($id, $name));
        } catch (InvalidProjectException) {
            return $this->json(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(ProjectResponseFactory::fromResult($result));
    }
}
