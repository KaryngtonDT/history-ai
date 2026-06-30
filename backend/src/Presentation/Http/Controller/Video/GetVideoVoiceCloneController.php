<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VoiceClone\Handlers\GetVideoVoiceCloneHandler;
use App\Application\VoiceClone\Queries\GetVideoVoiceCloneQuery;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use App\Presentation\Http\Response\VoiceClone\VideoVoiceCloneResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoVoiceCloneController extends AbstractController
{
    #[Route('/api/videos/{videoId}/voice-clone/{language}', name: 'api_videos_voice_clone_get', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, GetVideoVoiceCloneHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoVoiceCloneQuery($videoId, $language));
        } catch (InvalidVoiceCloneException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(VideoVoiceCloneResponse::fromResult($result)->toArray());
    }
}
