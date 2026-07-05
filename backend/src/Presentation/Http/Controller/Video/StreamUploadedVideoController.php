<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Video\Handlers\StreamUploadedVideoHandler;
use App\Domain\Video\Exception\InvalidVideoJobException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class StreamUploadedVideoController extends AbstractController
{
    #[Route('/api/videos/{videoId}/stream', name: 'api_videos_stream', methods: ['GET'])]
    public function __invoke(string $videoId, StreamUploadedVideoHandler $handler): Response
    {
        try {
            $path = $handler($videoId);
        } catch (InvalidVideoJobException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $response = new BinaryFileResponse(
            $path,
            Response::HTTP_OK,
            ['Content-Type' => $this->resolveVideoMimeType($path)],
        );
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($path),
        );

        return $response;
    }

    private function resolveVideoMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            'mov' => 'video/quicktime',
            default => 'video/mp4',
        };
    }
}
