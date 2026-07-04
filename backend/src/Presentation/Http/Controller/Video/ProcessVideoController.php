<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Video\VideoProcessingEnqueueService;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProcessVideoController extends AbstractController
{
    #[OA\Post(
        operationId: 'processVideo',
        summary: 'Enqueue video processing',
        description: 'Queues or re-queues a video for pipeline processing (e.g. after a failed transcription).',
        tags: ['Video'],
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
                response: 202,
                description: 'Processing queued',
            ),
            new OA\Response(
                response: 404,
                description: 'Video not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/process', name: 'api_videos_process_post', methods: ['POST'])]
    public function __invoke(string $videoId, VideoProcessingEnqueueService $enqueueService): JsonResponse
    {
        try {
            $id = new VideoId($videoId);
        } catch (InvalidVideoJobException) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        $queued = $enqueueService->enqueueIfNeeded($id, ProcessingMode::Manual);

        return $this->json(
            ['status' => $queued ? 'queued' : 'unchanged'],
            Response::HTTP_ACCEPTED,
        );
    }
}
