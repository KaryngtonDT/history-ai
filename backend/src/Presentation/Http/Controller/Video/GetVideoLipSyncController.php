<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\LipSync\Handlers\GetVideoLipSyncHandler;
use App\Application\LipSync\Queries\GetVideoLipSyncQuery;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Presentation\Http\Response\LipSync\VideoLipSyncResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoLipSyncController extends AbstractController
{
    #[Route('/api/videos/{videoId}/lip-sync/{language}', name: 'api_videos_lip_sync_get', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, GetVideoLipSyncHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoLipSyncQuery($videoId, $language));
        } catch (InvalidLipSyncException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(VideoLipSyncResponse::fromResult($result)->toArray());
    }
}
