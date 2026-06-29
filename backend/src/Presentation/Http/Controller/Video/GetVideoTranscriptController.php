<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Speech\Handlers\GetVideoTranscriptHandler;
use App\Application\Speech\Queries\GetVideoTranscriptQuery;
use App\Domain\Speech\Exception\InvalidTranscriptException;
use App\Presentation\Http\Response\Speech\VideoTranscriptResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoTranscriptController extends AbstractController
{
    #[Route('/api/videos/{videoId}/transcript', name: 'api_videos_transcript_get', methods: ['GET'])]
    public function __invoke(string $videoId, GetVideoTranscriptHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoTranscriptQuery($videoId));
        } catch (InvalidTranscriptException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(VideoTranscriptResponse::fromResult($result)->toArray());
    }
}
