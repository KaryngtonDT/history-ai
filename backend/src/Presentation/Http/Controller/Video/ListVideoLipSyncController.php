<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\LipSync\Handlers\ListVideoLipSyncHandler;
use App\Application\LipSync\Queries\ListVideoLipSyncQuery;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Presentation\Http\Response\LipSync\VideoLipSyncSummaryResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListVideoLipSyncController extends AbstractController
{
    #[OA\Get(
        operationId: 'listVideoLipSync',
        summary: 'List video lip sync artifacts',
        description: 'Returns summaries of lip-synced videos available for a video job.',
        tags: ['Video'],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                description: 'UUID of the uploaded video job.',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lip sync artifacts found',
                content: new OA\JsonContent(ref: '#/components/schemas/VideoLipSyncList'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/lip-sync', name: 'api_videos_lip_sync_list', methods: ['GET'])]
    public function __invoke(string $videoId, ListVideoLipSyncHandler $handler): JsonResponse
    {
        try {
            $summaries = $handler(new ListVideoLipSyncQuery($videoId));
        } catch (InvalidLipSyncException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'videoId' => $videoId,
            'lipSyncs' => array_map(
                static fn ($summary): array => VideoLipSyncSummaryResponse::fromSummary($summary)->toArray(),
                $summaries,
            ),
        ]);
    }
}
