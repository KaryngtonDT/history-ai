<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\TTS\Handlers\GetVideoAudioHandler;
use App\Application\TTS\Queries\GetVideoAudioQuery;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Presentation\Http\Response\TTS\VideoAudioResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoAudioController extends AbstractController
{
    #[Route('/api/videos/{videoId}/audio/{language}', name: 'api_videos_audio_get', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, GetVideoAudioHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoAudioQuery($videoId, $language));
        } catch (InvalidAudioArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(VideoAudioResponse::fromResult($result)->toArray());
    }
}
