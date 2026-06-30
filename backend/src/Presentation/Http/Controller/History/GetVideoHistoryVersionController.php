<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\History;

use App\Application\History\GetExecutionHistoryHandler;
use App\Application\History\Queries\GetExecutionHistoryQuery;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoHistoryVersionController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoHistoryVersion',
        summary: 'Get a specific execution version',
        tags: ['Execution History'],
        parameters: [
            new OA\Parameter(name: 'videoId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'version', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Execution version', content: new OA\JsonContent(ref: '#/components/schemas/ExecutionVersion')),
            new OA\Response(response: 404, description: 'Version not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/videos/{videoId}/history/{version}', name: 'api_videos_history_version', methods: ['GET'], requirements: ['version' => '\d+'])]
    public function __invoke(string $videoId, int $version, GetExecutionHistoryHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetExecutionHistoryQuery($videoId));
        } catch (InvalidExecutionHistoryException) {
            return $this->json(['error' => 'Execution history not found'], Response::HTTP_NOT_FOUND);
        }

        foreach ($result->versions as $entry) {
            if ($entry->versionNumber === $version) {
                return $this->json(HistoryResponseFactory::versionFromResult($entry));
            }
        }

        return $this->json(['error' => 'Execution version not found'], Response::HTTP_NOT_FOUND);
    }
}
