<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Project;

use App\Application\Workspace\Handlers\RemoveProjectVideoHandler;
use App\Domain\Workspace\Exception\InvalidProjectException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RemoveProjectVideoController extends AbstractController
{
    #[Route('/api/projects/{id}/videos/{videoId}', name: 'api_projects_videos_remove', methods: ['DELETE'])]
    public function __invoke(string $id, string $videoId, RemoveProjectVideoHandler $handler): JsonResponse
    {
        try {
            $result = $handler($id, $videoId);
        } catch (InvalidProjectException) {
            return $this->json(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(ProjectResponseFactory::fromResult($result));
    }
}
