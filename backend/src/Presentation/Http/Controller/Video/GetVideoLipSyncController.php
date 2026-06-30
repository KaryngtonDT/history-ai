<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\LipSync\Handlers\GetVideoLipSyncHandler;
use App\Application\LipSync\Queries\GetVideoLipSyncQuery;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Presentation\Http\Response\LipSync\VideoLipSyncResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoLipSyncController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoLipSync',
        summary: 'Get video lip sync',
        description: 'Returns lip-synced video metadata and stream URLs for a specific language.',
        tags: ['Video'],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
            new OA\Parameter(
                name: 'language',
                in: 'path',
                required: true,
                schema: new OA\Schema(ref: '#/components/schemas/TranslationLanguage'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lip sync found',
                content: new OA\JsonContent(ref: '#/components/schemas/LipSyncArtifact'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/lip-sync/{language}', name: 'api_videos_lip_sync_get', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, GetVideoLipSyncHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoLipSyncQuery($videoId, $language));
        } catch (InvalidLipSyncException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(VideoLipSyncResponse::fromResult($result)->toArray());
    }
}
