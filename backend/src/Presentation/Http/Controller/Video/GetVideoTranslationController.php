<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Translation\Handlers\GetVideoTranslationHandler;
use App\Application\Translation\Queries\GetVideoTranslationQuery;
use App\Domain\Translation\Exception\InvalidTranslationException;
use App\Presentation\Http\Response\Translation\VideoTranslationResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoTranslationController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoTranslation',
        summary: 'Get video translation',
        description: 'Returns the translated transcript for a specific target language. Available after translation artifacts are generated for the video.',
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
                description: 'Target language code for the translation.',
                schema: new OA\Schema(ref: '#/components/schemas/TranslationLanguage'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Translation found',
                content: new OA\JsonContent(ref: '#/components/schemas/Translation'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request or translation not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/translations/{language}', name: 'api_videos_translation_get', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, GetVideoTranslationHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoTranslationQuery($videoId, $language));
        } catch (InvalidTranslationException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(VideoTranslationResponse::fromResult($result)->toArray());
    }
}
