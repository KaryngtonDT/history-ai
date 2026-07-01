<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Project;

use App\Application\Workspace\Commands\RemoveProjectVideoCommand;
use App\Application\Workspace\Handlers\RemoveProjectVideoHandler;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Presentation\Http\CollaboratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RemoveProjectVideoController extends AbstractController
{
    #[Route('/api/projects/{id}/videos/{videoId}', name: 'api_projects_videos_remove', methods: ['DELETE'])]
    public function __invoke(string $id, string $videoId, Request $request, RemoveProjectVideoHandler $handler): JsonResponse
    {
        try {
            $collaborator = CollaboratorResolver::fromRequest($request);
            $result = $handler(new RemoveProjectVideoCommand($id, $videoId, $collaborator->userId));
        } catch (InvalidWorkspaceMemberException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (InvalidProjectException) {
            return $this->json(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(ProjectResponseFactory::fromResult($result));
    }
}
