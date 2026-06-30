<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\TTS\Handlers\GenerateVideoAudioHandler;
use App\Application\TTS\Commands\GenerateVideoAudioCommand;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GenerateVideoAudioController extends AbstractController
{
    #[OA\Post(
        operationId: 'generateVideoAudio',
        summary: 'Generate video audio',
        description: 'Synthesizes translated text into audio files using the configured text-to-speech provider. Creates one audio artifact per target language.',
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
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(ref: '#/components/schemas/GenerateVideoAudioRequest'),
        ),
        responses: [
            new OA\Response(
                response: 202,
                description: 'Audio generation accepted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'generated'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/audio', name: 'api_videos_audio_generate', methods: ['POST'])]
    public function __invoke(string $videoId, Request $request, GenerateVideoAudioHandler $handler): JsonResponse
    {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $targetLanguages = $payload['targetLanguages'] ?? [];
        $provider = $payload['provider'] ?? null;
        $voiceId = $payload['voiceId'] ?? null;

        if (!is_array($targetLanguages)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        /** @var list<string> $languageCodes */
        $languageCodes = array_values(array_filter(
            array_map(
                static fn (mixed $value): ?string => is_string($value) ? $value : null,
                $targetLanguages,
            ),
            static fn (?string $value): bool => null !== $value && '' !== trim($value),
        ));

        try {
            $handler(new GenerateVideoAudioCommand(
                videoId: $videoId,
                targetLanguages: $languageCodes,
                provider: is_string($provider) ? $provider : null,
                voiceId: is_string($voiceId) ? $voiceId : null,
            ));
        } catch (InvalidAudioArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'generated'], Response::HTTP_ACCEPTED);
    }
}
