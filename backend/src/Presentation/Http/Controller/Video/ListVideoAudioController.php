<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\TTS\Handlers\ListVideoAudioHandler;
use App\Application\TTS\Queries\ListVideoAudioQuery;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Presentation\Http\Response\TTS\VideoAudioSummaryResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListVideoAudioController extends AbstractController
{
    #[Route('/api/videos/{videoId}/audio', name: 'api_videos_audio_list', methods: ['GET'])]
    public function __invoke(string $videoId, ListVideoAudioHandler $handler): JsonResponse
    {
        try {
            $summaries = $handler(new ListVideoAudioQuery($videoId));
        } catch (InvalidAudioArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'videoId' => $videoId,
            'audio' => array_map(
                static fn ($summary): array => VideoAudioSummaryResponse::fromSummary($summary)->toArray(),
                $summaries,
            ),
        ]);
    }
}
