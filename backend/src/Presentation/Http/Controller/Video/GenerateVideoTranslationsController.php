<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Pipeline\Orchestration\PipelineLegacyStageLauncher;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\Exception\InvalidPipelineJobException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GenerateVideoTranslationsController extends AbstractController
{
    #[OA\Post(
        operationId: 'generateVideoTranslations',
        summary: 'Generate video translations',
        description: 'Starts a translation pipeline job with the selected languages and provider.',
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
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/GenerateVideoTranslationsRequest'),
        ),
        responses: [
            new OA\Response(
                response: 202,
                description: 'Translation pipeline job accepted',
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
    #[Route('/api/videos/{videoId}/translations', name: 'api_videos_translations_generate', methods: ['POST'])]
    public function __invoke(
        string $videoId,
        Request $request,
        PipelineLegacyStageLauncher $launcher,
    ): JsonResponse {
        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $targetLanguages = $payload['targetLanguages'] ?? null;
        $provider = $payload['provider'] ?? null;

        if (!is_array($targetLanguages) || [] === $targetLanguages) {
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
            $job = $launcher->launch($videoId, PipelineStageType::Translation, [
                'targetLanguages' => $languageCodes,
                'provider' => is_string($provider) ? $provider : null,
            ]);
        } catch (InvalidPipelineJobException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_CONFLICT);
        }

        return $this->json(['status' => 'accepted', 'job' => $job], Response::HTTP_ACCEPTED);
    }
}
