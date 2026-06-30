<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\LipSync\Handlers\StreamVideoLipSyncHandler;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class StreamVideoLipSyncController extends AbstractController
{
    #[Route('/api/videos/{videoId}/lip-sync/{language}/stream', name: 'api_videos_lip_sync_stream', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, StreamVideoLipSyncHandler $handler): Response
    {
        try {
            $path = $handler($videoId, $language);
        } catch (InvalidLipSyncException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($path),
        );

        return $response;
    }
}
