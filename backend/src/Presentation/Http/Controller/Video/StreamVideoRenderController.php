<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\VideoRender\Handlers\StreamVideoRenderHandler;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class StreamVideoRenderController extends AbstractController
{
    #[OA\Get(
        operationId: 'streamVideoRender',
        summary: 'Stream or download final rendered MP4',
        description: 'Streams the final rendered MP4 file for preview or download.',
        tags: ['Video'],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
            new OA\Parameter(
                name: 'language',
                in: 'path',
                required: true,
                schema: new OA\Schema(ref: '#/components/schemas/TranslationLanguage'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Final MP4 file',
                content: new OA\MediaType(
                    mediaType: 'video/mp4',
                    schema: new OA\Schema(type: 'string', format: 'binary'),
                ),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/render/{language}/stream', name: 'api_videos_render_stream', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, StreamVideoRenderHandler $handler): Response
    {
        try {
            $path = $handler($videoId, $language);
        } catch (InvalidVideoRenderException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $response = new BinaryFileResponse(
            $path,
            Response::HTTP_OK,
            ['Content-Type' => 'video/mp4'],
        );
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($path),
        );

        return $response;
    }
}
