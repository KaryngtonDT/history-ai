<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Speech\Handlers\GetVideoTranscriptHandler;
use App\Application\Speech\Queries\GetVideoTranscriptQuery;
use App\Domain\Speech\Exception\InvalidTranscriptException;
use App\Domain\Speech\Exception\TranscriptNotFoundException;
use App\Presentation\Http\Response\Speech\VideoTranscriptResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoTranscriptController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoTranscript',
        summary: 'Get video transcript',
        description: 'Returns the speech-to-text transcript for a processed video, including segmented timestamps. Available after the video job completes and a transcript artifact is generated.',
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
                description: 'Transcript found',
                content: new OA\JsonContent(ref: '#/components/schemas/Transcript'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request or transcript not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/transcript', name: 'api_videos_transcript_get', methods: ['GET'])]
    public function __invoke(string $videoId, GetVideoTranscriptHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoTranscriptQuery($videoId));
        } catch (TranscriptNotFoundException $exception) {
            return $this->json($exception->toPayload(), Response::HTTP_BAD_REQUEST);
        } catch (InvalidTranscriptException $exception) {
            return $this->json([
                'error' => 'invalid_transcript_request',
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(VideoTranscriptResponse::fromResult($result)->toArray());
    }
}
