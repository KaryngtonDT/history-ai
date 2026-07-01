<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Telemetry;

use App\Application\Telemetry\GetWorkspaceAnalyticsHandler;
use App\Application\Telemetry\Queries\GetWorkspaceAnalyticsQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetWorkspaceAnalyticsController extends AbstractController
{
    #[OA\Get(
        operationId: 'getWorkspaceAnalytics',
        summary: 'Get aggregated workspace analytics',
        tags: ['Telemetry'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Workspace analytics summary',
                content: new OA\JsonContent(ref: '#/components/schemas/WorkspaceAnalytics'),
            ),
        ],
    )]
    #[Route('/api/workspaces/{id}/analytics', name: 'api_workspaces_analytics', methods: ['GET'])]
    public function __invoke(string $id, GetWorkspaceAnalyticsHandler $handler): JsonResponse
    {
        return $this->json(
            TelemetryResponseFactory::analyticsFromResult($handler(new GetWorkspaceAnalyticsQuery($id))),
        );
    }
}
