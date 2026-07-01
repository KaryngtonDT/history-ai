<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collaboration;

use App\Application\Collaboration\Commands\RemoveWorkspaceMemberCommand;
use App\Application\Collaboration\RemoveWorkspaceMemberHandler;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Presentation\Http\CollaboratorResolver;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RemoveWorkspaceMemberController extends AbstractController
{
    #[OA\Delete(
        operationId: 'removeWorkspaceMember',
        summary: 'Remove a member from a workspace',
        tags: ['Collaboration'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'memberId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Member removed'),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/workspaces/{id}/members/{memberId}', name: 'api_workspaces_members_remove', methods: ['DELETE'])]
    public function __invoke(string $id, string $memberId, Request $request, RemoveWorkspaceMemberHandler $handler): JsonResponse
    {
        try {
            $collaborator = CollaboratorResolver::fromRequest($request);
            $handler(new RemoveWorkspaceMemberCommand($id, $memberId, $collaborator->userId));
        } catch (InvalidWorkspaceMemberException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
