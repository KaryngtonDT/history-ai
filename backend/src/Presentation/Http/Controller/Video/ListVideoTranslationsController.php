<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Translation\Handlers\ListVideoTranslationsHandler;
use App\Application\Translation\Queries\ListVideoTranslationsQuery;
use App\Domain\Translation\Exception\InvalidTranslationException;
use App\Presentation\Http\Response\Translation\VideoTranslationSummaryResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListVideoTranslationsController extends AbstractController
{
    #[OA\Get(
        operationId: 'listVideoTranslations',
        summary: 'List video translations',
        description: 'Returns summaries of all translated transcripts available for a video job.',
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
                description: 'Translations found',
                content: new OA\JsonContent(ref: '#/components/schemas/VideoTranslationsList'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/translations', name: 'api_videos_translations_list', methods: ['GET'])]
    public function __invoke(string $videoId, ListVideoTranslationsHandler $handler): JsonResponse
    {
        try {
            $summaries = $handler(new ListVideoTranslationsQuery($videoId));
        } catch (InvalidTranslationException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'videoId' => $videoId,
            'translations' => array_map(
                static fn ($summary): array => VideoTranslationSummaryResponse::fromSummary($summary)->toArray(),
                $summaries,
            ),
        ]);
    }
}
