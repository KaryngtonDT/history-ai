<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VideoRender\Handlers\GetVideoRenderHandler;
use App\Application\VideoRender\Queries\GetVideoRenderQuery;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use App\Presentation\Http\Response\VideoRender\VideoRenderResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoRenderController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoRender',
        summary: 'Get final rendered video metadata',
        description: 'Returns final render metadata including stream and download URLs.',
        tags: ['Video'],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
            new OA\Parameter(
                name: 'language',
                in: 'path',
                required: true,
                schema: new OA\Schema(ref: '#/components/schemas/TranslationLanguage'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Final render metadata',
                content: new OA\JsonContent(ref: '#/components/schemas/FinalVideoArtifact'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/render/{language}', name: 'api_videos_render_get', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, GetVideoRenderHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoRenderQuery($videoId, $language));
        } catch (InvalidVideoRenderException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(VideoRenderResponse::fromResult($result)->toArray());
    }
}
