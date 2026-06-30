<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\TTS\Handlers\ListVideoAudioHandler;
use App\Application\TTS\Queries\ListVideoAudioQuery;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Presentation\Http\Response\TTS\VideoAudioSummaryResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListVideoAudioController extends AbstractController
{
    #[OA\Get(
        operationId: 'listVideoAudio',
        summary: 'List video audio',
        description: 'Returns summaries of synthesized audio available for a video job.',
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
                description: 'Audio found',
                content: new OA\JsonContent(ref: '#/components/schemas/VideoAudioList'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/audio', name: 'api_videos_audio_list', methods: ['GET'])]
    public function __invoke(string $videoId, ListVideoAudioHandler $handler): JsonResponse
    {
        try {
            $summaries = $handler(new ListVideoAudioQuery($videoId));
        } catch (InvalidAudioArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'videoId' => $videoId,
            'audio' => array_map(
                static fn ($summary): array => VideoAudioSummaryResponse::fromSummary($summary)->toArray(),
                $summaries,
            ),
        ]);
    }
}
