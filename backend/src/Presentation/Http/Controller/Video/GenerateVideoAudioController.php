<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Pipeline\Orchestration\PipelineLegacyStageLauncher;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\Exception\InvalidPipelineJobException;
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
        description: 'Starts a text-to-speech pipeline job for the selected languages.',
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
                description: 'Audio pipeline job accepted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'accepted'),
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
    public function __invoke(
        string $videoId,
        Request $request,
        PipelineLegacyStageLauncher $launcher,
    ): JsonResponse {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            $payload = [];
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
            $job = $launcher->launch($videoId, PipelineStageType::TextToSpeech, [
                'targetLanguages' => $languageCodes,
                'provider' => is_string($provider) ? $provider : null,
                'voiceId' => is_string($voiceId) ? $voiceId : null,
            ]);
        } catch (InvalidPipelineJobException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_CONFLICT);
        } catch (InvalidAudioArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'accepted', 'job' => $job], Response::HTTP_ACCEPTED);
    }
}
