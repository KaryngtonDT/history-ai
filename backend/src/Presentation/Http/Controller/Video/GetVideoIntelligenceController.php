<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VideoIntelligence\Handlers\GetVideoIntelligenceHandler;
use App\Application\VideoIntelligence\Queries\GetVideoIntelligenceQuery;
use App\Domain\Video\Exception\InvalidVideoJobException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoIntelligenceController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoIntelligence',
        summary: 'Get video intelligence report',
        description: 'Returns the AI Director video intelligence analysis for a video.',
        tags: ['Video Intelligence'],
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
                description: 'Video intelligence report',
                content: new OA\JsonContent(ref: '#/components/schemas/VideoIntelligence'),
            ),
            new OA\Response(
                response: 404,
                description: 'Video not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/intelligence', name: 'api_videos_intelligence_get', methods: ['GET'])]
    public function __invoke(string $videoId, GetVideoIntelligenceHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoIntelligenceQuery($videoId));
        } catch (InvalidVideoJobException) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $result->id,
            'videoId' => $result->videoId,
            'durationSeconds' => $result->durationSeconds,
            'scene' => $result->scene,
            'audio' => [
                'language' => $result->language,
                'speakerCount' => $result->speakerCount,
                'backgroundNoise' => $result->backgroundNoise,
                'backgroundMusic' => $result->backgroundMusic,
                'speechSpeed' => $result->speechSpeed,
                'confidence' => $result->confidence,
            ],
            'visual' => [
                'resolution' => $result->resolution,
                'fps' => $result->fps,
                'lighting' => $result->lighting,
                'lipVisibility' => $result->lipVisibility,
                'faceCount' => $result->faceCount,
            ],
            'speech' => [
                'dominantEmotion' => $result->dominantEmotion,
                'averageSpeakingRate' => $result->averageSpeakingRate,
                'pauseCount' => $result->pauseCount,
                'hasOverlaps' => $result->hasOverlaps,
            ],
            'speakers' => $result->speakers,
            'gpuAvailable' => $result->gpuAvailable,
            'estimatedVramGb' => $result->estimatedVramGb,
        ]);
    }
}
