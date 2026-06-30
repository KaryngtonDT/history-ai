<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Scheduler\Handlers\GetExecutionScheduleHandler;
use App\Application\Scheduler\Queries\GetExecutionScheduleQuery;
use App\Domain\Video\Exception\InvalidVideoJobException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoScheduleController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoSchedule',
        summary: 'Get execution schedule for a video',
        description: 'Returns resource-aware pipeline execution schedule for a video.',
        tags: ['Scheduler'],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Execution schedule',
                content: new OA\JsonContent(ref: '#/components/schemas/ExecutionSchedule'),
            ),
            new OA\Response(
                response: 404,
                description: 'Video not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/schedule', name: 'api_videos_schedule_get', methods: ['GET'])]
    public function __invoke(string $videoId, GetExecutionScheduleHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetExecutionScheduleQuery($videoId));
        } catch (InvalidVideoJobException) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $result->id,
            'videoId' => $result->videoId,
            'strategy' => $result->strategy,
            'estimatedCompletionSeconds' => $result->estimatedCompletionSeconds,
            'currentStage' => $result->currentStage,
            'currentResource' => $result->currentResource,
            'stages' => $result->stages,
            'resources' => $result->resources,
        ]);
    }
}
