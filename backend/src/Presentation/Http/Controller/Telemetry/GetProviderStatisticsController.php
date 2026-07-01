<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Telemetry;

use App\Application\Telemetry\GetProviderStatisticsHandler;
use App\Application\Telemetry\Queries\GetProviderStatisticsQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetProviderStatisticsController extends AbstractController
{
    #[OA\Get(
        operationId: 'getWorkspaceProviderStatistics',
        summary: 'Get provider usage statistics for a workspace',
        tags: ['Telemetry'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Provider statistics',
                content: new OA\JsonContent(ref: '#/components/schemas/ProviderStatistics'),
            ),
        ],
    )]
    #[Route('/api/workspaces/{id}/providers', name: 'api_workspaces_providers', methods: ['GET'])]
    public function __invoke(string $id, GetProviderStatisticsHandler $handler): JsonResponse
    {
        return $this->json(
            TelemetryResponseFactory::providerStatisticsFromResult($handler(new GetProviderStatisticsQuery($id))),
        );
    }
}
