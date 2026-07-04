<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Video\Handlers\GetVideoStatusHandler;
use App\Application\Video\Queries\GetVideoStatusQuery;
use App\Domain\Video\Exception\InvalidVideoJobException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoStatusController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoStatus',
        summary: 'Get video processing status',
        description: 'Returns the lifecycle status of a video processing job.',
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
                response: 200,
                description: 'Video job status',
                content: new OA\JsonContent(ref: '#/components/schemas/VideoStatusResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Video not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/status', name: 'api_videos_status_get', methods: ['GET'])]
    public function __invoke(string $videoId, GetVideoStatusHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoStatusQuery($videoId));
        } catch (InvalidVideoJobException) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'videoId' => $result->videoId,
            'status' => $result->status,
            'originalFilename' => $result->originalFilename,
            'language' => $result->language,
            'createdAt' => $result->createdAt,
        ]);
    }
}
