<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collaboration;

use App\Application\Collaboration\Commands\InviteWorkspaceMemberCommand;
use App\Application\Collaboration\InviteWorkspaceMemberHandler;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Collaboration\WorkspaceRole;
use App\Presentation\Http\CollaboratorResolver;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InviteWorkspaceMemberController extends AbstractController
{
    #[OA\Post(
        operationId: 'inviteWorkspaceMember',
        summary: 'Invite a member to a workspace',
        tags: ['Collaboration'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/InviteWorkspaceMemberRequest'),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Invitation created', content: new OA\JsonContent(ref: '#/components/schemas/WorkspaceInvitation')),
            new OA\Response(response: 403, description: 'Forbidden', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/workspaces/{id}/members', name: 'api_workspaces_members_invite', methods: ['POST'])]
    public function __invoke(string $id, Request $request, InviteWorkspaceMemberHandler $handler): JsonResponse
    {
        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);
        $email = is_array($payload) && is_string($payload['email'] ?? null) ? trim($payload['email']) : '';
        $roleValue = is_array($payload) && is_string($payload['role'] ?? null) ? $payload['role'] : '';
        $role = WorkspaceRole::tryFrom($roleValue);
        $displayName = is_array($payload) && is_string($payload['displayName'] ?? null)
            ? trim($payload['displayName'])
            : '';

        if ('' === $email || null === $role) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $collaborator = CollaboratorResolver::fromRequest($request);
            $result = $handler(new InviteWorkspaceMemberCommand(
                $id,
                $collaborator->userId,
                $email,
                $role,
                $displayName,
            ));
        } catch (InvalidWorkspaceMemberException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
        }

        return $this->json(
            CollaborationResponseFactory::invitationFromResult($result),
            Response::HTTP_CREATED,
        );
    }
}
