<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Telemetry;

use App\Application\Telemetry\ListWorkspaceTelemetryHandler;
use App\Application\Telemetry\Queries\ListWorkspaceTelemetryQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListWorkspaceTelemetryController extends AbstractController
{
    #[OA\Get(
        operationId: 'listWorkspaceTelemetry',
        summary: 'List pipeline telemetry records for a workspace',
        tags: ['Telemetry'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Pipeline telemetry records',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/PipelineTelemetry'),
                ),
            ),
        ],
    )]
    #[Route('/api/workspaces/{id}/telemetry', name: 'api_workspaces_telemetry', methods: ['GET'])]
    public function __invoke(string $id, ListWorkspaceTelemetryHandler $handler): JsonResponse
    {
        return $this->json(array_map(
            static fn ($record): array => TelemetryResponseFactory::telemetryFromResult($record),
            $handler(new ListWorkspaceTelemetryQuery($id)),
        ));
    }
}
