<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Video\Commands\UploadVideoCommand;
use App\Application\Video\Handlers\UploadVideoHandler;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Presentation\Http\Response\Video\UploadVideoResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UploadVideoController extends AbstractController
{
    #[OA\Post(
        operationId: 'uploadVideo',
        summary: 'Upload video',
        description: 'Accepts a multipart video file (mp4, mov, or mkv), stores it locally, persists a VideoJob, and queues it for future processing. Maximum upload size is controlled by VIDEO_UPLOAD_MAX_BYTES (default 500 MB).',
        tags: ['Video'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['video'],
                    properties: [
                        new OA\Property(
                            property: 'video',
                            description: 'Video file. Supported extensions: mp4, mov, mkv.',
                            type: 'string',
                            format: 'binary',
                        ),
                    ],
                ),
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Video uploaded and queued',
                content: new OA\JsonContent(ref: '#/components/schemas/UploadVideoResponse'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos', name: 'api_videos_upload', methods: ['POST'])]
    public function __invoke(Request $request, UploadVideoHandler $handler): JsonResponse
    {
        $uploadedFile = $request->files->get('video');

        if (!$uploadedFile instanceof UploadedFile || !$uploadedFile->isValid()) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new UploadVideoCommand(
                originalFilename: (string) $uploadedFile->getClientOriginalName(),
                fileSizeBytes: (int) $uploadedFile->getSize(),
                temporaryPath: $uploadedFile->getPathname(),
                processingMode: $this->parseProcessingMode($request->request->get('processingMode')),
                strategy: $this->parseStrategy($request->request->get('strategy')),
            ));
        } catch (InvalidVideoJobException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(
            UploadVideoResponse::fromResult($result)->toArray(),
            Response::HTTP_CREATED,
        );
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }

    private function parseProcessingMode(mixed $value): ProcessingMode
    {
        if (!is_string($value)) {
            return ProcessingMode::Manual;
        }

        return ProcessingMode::tryFrom($value) ?? ProcessingMode::Manual;
    }

    private function parseStrategy(mixed $value): ?ProcessingStrategy
    {
        if (!is_string($value) || '' === trim($value)) {
            return null;
        }

        return ProcessingStrategy::tryFrom($value);
    }
}
