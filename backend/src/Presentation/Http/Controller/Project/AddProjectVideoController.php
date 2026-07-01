<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Project;

use App\Application\Workspace\Commands\AddProjectVideoCommand;
use App\Application\Workspace\Handlers\AddProjectVideoHandler;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Presentation\Http\CollaboratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AddProjectVideoController extends AbstractController
{
    #[Route('/api/projects/{id}/videos', name: 'api_projects_videos_add', methods: ['POST'])]
    public function __invoke(string $id, Request $request, AddProjectVideoHandler $handler): JsonResponse
    {
        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);
        $videoId = is_array($payload) && is_string($payload['videoId'] ?? null) ? $payload['videoId'] : '';

        if ('' === $videoId) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $collaborator = CollaboratorResolver::fromRequest($request);
            $result = $handler(new AddProjectVideoCommand($id, $videoId, $collaborator->userId));
        } catch (InvalidWorkspaceMemberException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (InvalidProjectException $exception) {
            $status = str_contains($exception->getMessage(), 'Video not found')
                ? Response::HTTP_BAD_REQUEST
                : Response::HTTP_NOT_FOUND;

            return $this->json(['error' => $exception->getMessage()], $status);
        }

        return $this->json(ProjectResponseFactory::fromResult($result));
    }
}
