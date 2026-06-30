<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\LipSync\Commands\GenerateVideoLipSyncCommand;
use App\Application\LipSync\Handlers\GenerateVideoLipSyncHandler;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GenerateVideoLipSyncController extends AbstractController
{
    #[OA\Post(
        operationId: 'generateVideoLipSync',
        summary: 'Generate video lip sync',
        description: 'Synchronizes lip movements with cloned audio using the configured lip sync provider.',
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
            content: new OA\JsonContent(ref: '#/components/schemas/GenerateVideoLipSyncRequest'),
        ),
        responses: [
            new OA\Response(
                response: 202,
                description: 'Lip sync generation accepted',
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
    #[Route('/api/videos/{videoId}/lip-sync', name: 'api_videos_lip_sync_generate', methods: ['POST'])]
    public function __invoke(string $videoId, Request $request, GenerateVideoLipSyncHandler $handler): JsonResponse
    {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            $payload = [];
        }

        $targetLanguages = $payload['targetLanguages'] ?? [];
        $provider = $payload['provider'] ?? null;

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
            $handler(new GenerateVideoLipSyncCommand(
                videoId: $videoId,
                targetLanguages: $languageCodes,
                provider: is_string($provider) ? $provider : null,
            ));
        } catch (InvalidLipSyncException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'generated'], Response::HTTP_ACCEPTED);
    }
}
