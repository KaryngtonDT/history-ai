<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\TTS\Handlers\GetVideoAudioHandler;
use App\Application\TTS\Queries\GetVideoAudioQuery;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Presentation\Http\Response\TTS\VideoAudioResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoAudioController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoAudio',
        summary: 'Get video audio',
        description: 'Returns synthesized audio metadata for a specific target language.',
        tags: ['Video'],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                description: 'UUID of the uploaded video job.',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
            new OA\Parameter(
                name: 'language',
                in: 'path',
                required: true,
                description: 'Target language code for the audio.',
                schema: new OA\Schema(ref: '#/components/schemas/TranslationLanguage'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Audio found',
                content: new OA\JsonContent(ref: '#/components/schemas/AudioArtifact'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request or audio not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/audio/{language}', name: 'api_videos_audio_get', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, GetVideoAudioHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoAudioQuery($videoId, $language));
        } catch (InvalidAudioArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(VideoAudioResponse::fromResult($result)->toArray());
    }
}
