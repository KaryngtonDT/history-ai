<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\Handlers\GetShadowContextHandler;
use App\Application\Shadow\Queries\GetShadowContextQuery;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Presentation\Http\Response\Shadow\WatchContextResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetShadowContextController extends AbstractController
{
    #[OA\Get(
        operationId: 'getShadowContext',
        summary: 'Get Shadow watch context for a video timestamp',
        description: 'Resolves transcript and translation segments around the current playback time for Shadow watch mode.',
        tags: ['Shadow'],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                description: 'UUID of the video.',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
            new OA\Parameter(
                name: 'time',
                in: 'query',
                required: true,
                description: 'Current playback time in seconds.',
                schema: new OA\Schema(type: 'number', format: 'float'),
            ),
            new OA\Parameter(
                name: 'language',
                in: 'query',
                required: true,
                description: 'Target translation language code (for example fr or de).',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'conversationId',
                in: 'query',
                required: false,
                description: 'Optional conversation id for memory context.',
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Watch context resolved',
                content: new OA\JsonContent(ref: '#/components/schemas/WatchContext'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request or missing transcript',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/context',
        name: 'api_videos_shadow_context_get',
        methods: ['GET'],
    )]
    public function __invoke(
        string $videoId,
        Request $request,
        GetShadowContextHandler $handler,
    ): JsonResponse {
        $time = $request->query->get('time');
        $language = $request->query->getString('language');

        if (!is_numeric($time)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        if ('' === trim($language)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $handler(new GetShadowContextQuery(
                videoId: $videoId,
                time: (float) $time,
                language: $language,
                conversationId: $request->query->get('conversationId'),
            ));
        } catch (InvalidShadowSessionException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(WatchContextResponse::fromResult($result)->toArray());
    }
}
