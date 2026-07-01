<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collaboration;

use App\Application\Collaboration\ListWorkspaceInvitationsHandler;
use App\Application\Collaboration\Queries\ListWorkspaceInvitationsQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListWorkspaceInvitationsController extends AbstractController
{
    #[OA\Get(
        operationId: 'listWorkspaceInvitations',
        summary: 'List pending workspace invitations',
        tags: ['Collaboration'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Workspace invitations',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/WorkspaceInvitation')),
            ),
        ],
    )]
    #[Route('/api/workspaces/{id}/invitations', name: 'api_workspaces_invitations_list', methods: ['GET'])]
    public function __invoke(string $id, ListWorkspaceInvitationsHandler $handler): JsonResponse
    {
        $invitations = $handler(new ListWorkspaceInvitationsQuery($id));

        return $this->json(array_map(
            static fn ($invitation): array => CollaborationResponseFactory::invitationFromResult($invitation),
            $invitations,
        ));
    }
}
