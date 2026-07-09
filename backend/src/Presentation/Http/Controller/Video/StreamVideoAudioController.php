<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\TTS\Handlers\StreamVideoAudioHandler;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class StreamVideoAudioController extends AbstractController
{
    #[Route('/api/videos/{videoId}/audio/{language}/stream', name: 'api_videos_audio_stream', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, StreamVideoAudioHandler $handler): Response
    {
        try {
            $path = $handler($videoId, $language);
        } catch (InvalidAudioArtifactException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $response = new BinaryFileResponse(
            $path,
            Response::HTTP_OK,
            ['Content-Type' => $this->resolveAudioMimeType($path)],
        );
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($path),
        );

        return $response;
    }

    private function resolveAudioMimeType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'flac' => 'audio/flac',
            'aac' => 'audio/aac',
            default => 'audio/wav',
        };
    }
}
