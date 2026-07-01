<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Collaboration;

use App\Application\Collaboration\ListWorkspaceMembersHandler;
use App\Application\Collaboration\Queries\ListWorkspaceMembersQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListWorkspaceMembersController extends AbstractController
{
    #[OA\Get(
        operationId: 'listWorkspaceMembers',
        summary: 'List workspace members',
        tags: ['Collaboration'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Workspace members',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/WorkspaceMember')),
            ),
        ],
    )]
    #[Route('/api/workspaces/{id}/members', name: 'api_workspaces_members_list', methods: ['GET'])]
    public function __invoke(string $id, ListWorkspaceMembersHandler $handler): JsonResponse
    {
        $members = $handler(new ListWorkspaceMembersQuery($id));

        return $this->json(array_map(
            static fn ($member): array => CollaborationResponseFactory::memberFromResult($member),
            $members,
        ));
    }
}
