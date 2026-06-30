<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Project;

use App\Application\Workspace\Handlers\DeleteProjectHandler;
use App\Domain\Workspace\Exception\InvalidProjectException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteProjectController extends AbstractController
{
    #[Route('/api/projects/{id}', name: 'api_projects_delete', methods: ['DELETE'])]
    public function __invoke(string $id, DeleteProjectHandler $handler): JsonResponse
    {
        try {
            $handler($id);
        } catch (InvalidProjectException) {
            return $this->json(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
