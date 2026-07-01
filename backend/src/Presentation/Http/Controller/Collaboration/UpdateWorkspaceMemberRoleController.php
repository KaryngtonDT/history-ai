<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collaboration;

use App\Application\Collaboration\Commands\UpdateWorkspaceMemberRoleCommand;
use App\Application\Collaboration\UpdateWorkspaceMemberRoleHandler;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Collaboration\WorkspaceRole;
use App\Presentation\Http\CollaboratorResolver;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateWorkspaceMemberRoleController extends AbstractController
{
    #[OA\Patch(
        operationId: 'updateWorkspaceMemberRole',
        summary: 'Update a workspace member role',
        tags: ['Collaboration'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'memberId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateWorkspaceMemberRoleRequest'),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Member updated', content: new OA\JsonContent(ref: '#/components/schemas/WorkspaceMember')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/workspaces/{id}/members/{memberId}', name: 'api_workspaces_members_update_role', methods: ['PATCH'])]
    public function __invoke(string $id, string $memberId, Request $request, UpdateWorkspaceMemberRoleHandler $handler): JsonResponse
    {
        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);
        $roleValue = is_array($payload) && is_string($payload['role'] ?? null) ? $payload['role'] : '';
        $role = WorkspaceRole::tryFrom($roleValue);

        if (null === $role) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $collaborator = CollaboratorResolver::fromRequest($request);
            $result = $handler(new UpdateWorkspaceMemberRoleCommand(
                $id,
                $memberId,
                $collaborator->userId,
                $role,
            ));
        } catch (InvalidWorkspaceMemberException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
        }

        return $this->json(CollaborationResponseFactory::memberFromResult($result));
    }
}
