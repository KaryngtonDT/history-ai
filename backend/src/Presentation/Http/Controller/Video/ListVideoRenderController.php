<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VideoRender\Handlers\ListVideoRenderHandler;
use App\Application\VideoRender\Queries\ListVideoRenderQuery;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use App\Presentation\Http\Response\VideoRender\VideoRenderSummaryResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListVideoRenderController extends AbstractController
{
    #[OA\Get(
        operationId: 'listVideoRender',
        summary: 'List final rendered videos',
        description: 'Returns summaries of final rendered MP4 artifacts for a video job.',
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
                description: 'Final render artifacts found',
                content: new OA\JsonContent(ref: '#/components/schemas/VideoRenderList'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/render', name: 'api_videos_render_list', methods: ['GET'])]
    public function __invoke(string $videoId, ListVideoRenderHandler $handler): JsonResponse
    {
        try {
            $summaries = $handler(new ListVideoRenderQuery($videoId));
        } catch (InvalidVideoRenderException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'videoId' => $videoId,
            'renders' => array_map(
                static fn ($summary): array => VideoRenderSummaryResponse::fromSummary($summary)->toArray(),
                $summaries,
            ),
        ]);
    }
}
