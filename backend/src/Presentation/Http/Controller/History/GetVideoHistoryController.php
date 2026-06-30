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

final class GetVideoHistoryController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoHistory',
        summary: 'Get execution history for a video',
        tags: ['Execution History'],
        parameters: [
            new OA\Parameter(name: 'videoId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Execution history', content: new OA\JsonContent(ref: '#/components/schemas/ExecutionHistory')),
            new OA\Response(response: 404, description: 'History not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/videos/{videoId}/history', name: 'api_videos_history_list', methods: ['GET'])]
    public function __invoke(string $videoId, GetExecutionHistoryHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetExecutionHistoryQuery($videoId));
        } catch (InvalidExecutionHistoryException) {
            return $this->json(['error' => 'Execution history not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $result->id,
            'videoId' => $result->videoId,
            'versions' => array_map(
                static fn ($version): array => HistoryResponseFactory::versionFromResult($version),
                $result->versions,
            ),
        ]);
    }
}
