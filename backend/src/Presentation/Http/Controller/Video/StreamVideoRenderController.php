<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VideoRender\Handlers\StreamVideoRenderHandler;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class StreamVideoRenderController extends AbstractController
{
    #[Route('/api/videos/{videoId}/render/{language}/stream', name: 'api_videos_render_stream', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, StreamVideoRenderHandler $handler): Response
    {
        try {
            $path = $handler($videoId, $language);
        } catch (InvalidVideoRenderException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($path),
        );

        return $response;
    }
}
