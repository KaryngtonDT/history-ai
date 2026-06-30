<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VoiceClone\Commands\GenerateVideoVoiceCloneCommand;
use App\Application\VoiceClone\Handlers\GenerateVideoVoiceCloneHandler;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GenerateVideoVoiceCloneController extends AbstractController
{
    #[OA\Post(
        operationId: 'generateVideoVoiceClone',
        summary: 'Generate video voice clone',
        description: 'Clones the original speaker voice onto translated generic audio using the configured voice clone provider.',
        tags: ['Video'],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(ref: '#/components/schemas/GenerateVideoVoiceCloneRequest'),
        ),
        responses: [
            new OA\Response(
                response: 202,
                description: 'Voice clone generation accepted',
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
    #[Route('/api/videos/{videoId}/voice-clone', name: 'api_videos_voice_clone_generate', methods: ['POST'])]
    public function __invoke(string $videoId, Request $request, GenerateVideoVoiceCloneHandler $handler): JsonResponse
    {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $targetLanguages = $payload['targetLanguages'] ?? [];
        $provider = $payload['provider'] ?? null;
        $voiceMode = $payload['voiceMode'] ?? null;

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
            $handler(new GenerateVideoVoiceCloneCommand(
                videoId: $videoId,
                targetLanguages: $languageCodes,
                provider: is_string($provider) ? $provider : null,
                voiceMode: is_string($voiceMode) ? $voiceMode : null,
            ));
        } catch (InvalidVoiceCloneException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'generated'], Response::HTTP_ACCEPTED);
    }
}
