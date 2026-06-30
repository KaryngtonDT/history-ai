<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VoiceClone\Handlers\ListVideoVoiceCloneHandler;
use App\Application\VoiceClone\Queries\ListVideoVoiceCloneQuery;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use App\Presentation\Http\Response\VoiceClone\VideoVoiceCloneSummaryResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListVideoVoiceCloneController extends AbstractController
{
    #[OA\Get(
        operationId: 'listVideoVoiceClone',
        summary: 'List video voice clones',
        description: 'Returns summaries of cloned voice audio available for a video job.',
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
                description: 'Voice clones found',
                content: new OA\JsonContent(ref: '#/components/schemas/VideoVoiceCloneList'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/voice-clone', name: 'api_videos_voice_clone_list', methods: ['GET'])]
    public function __invoke(string $videoId, ListVideoVoiceCloneHandler $handler): JsonResponse
    {
        try {
            $summaries = $handler(new ListVideoVoiceCloneQuery($videoId));
        } catch (InvalidVoiceCloneException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'videoId' => $videoId,
            'voiceClones' => array_map(
                static fn ($summary): array => VideoVoiceCloneSummaryResponse::fromSummary($summary)->toArray(),
                $summaries,
            ),
        ]);
    }
}
