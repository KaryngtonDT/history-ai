<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VoiceClone\Handlers\ListVideoVoiceCloneHandler;
use App\Application\VoiceClone\Queries\ListVideoVoiceCloneQuery;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use App\Presentation\Http\Response\VoiceClone\VideoVoiceCloneSummaryResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListVideoVoiceCloneController extends AbstractController
{
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
