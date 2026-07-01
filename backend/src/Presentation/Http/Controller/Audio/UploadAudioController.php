<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Audio;

use App\Application\AudioUpload\Commands\UploadAudioCommand;
use App\Application\AudioUpload\Handlers\UploadAudioHandler;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Source\Exception\InvalidSourceException;
use App\Presentation\Http\Response\Audio\UploadAudioResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UploadAudioController extends AbstractController
{
    #[OA\Post(
        operationId: 'uploadAudio',
        summary: 'Upload audio source',
        description: 'Accepts a multipart audio file (mp3, wav, flac, m4a, ogg), stores it locally, persists a Source record, and queues it for processing.',
        tags: ['Audio'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['audio'],
                    properties: [
                        new OA\Property(
                            property: 'audio',
                            description: 'Audio file. Supported extensions: mp3, wav, flac, m4a, ogg.',
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
                description: 'Audio uploaded and queued',
                content: new OA\JsonContent(ref: '#/components/schemas/UploadAudioResponse'),
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/audio', name: 'api_audio_upload', methods: ['POST'])]
    public function __invoke(Request $request, UploadAudioHandler $handler): JsonResponse
    {
        $uploadedFile = $request->files->get('audio');

        if (!$uploadedFile instanceof UploadedFile || !$uploadedFile->isValid()) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new UploadAudioCommand(
                originalFilename: (string) $uploadedFile->getClientOriginalName(),
                fileSizeBytes: (int) $uploadedFile->getSize(),
                temporaryPath: $uploadedFile->getPathname(),
                processingMode: $this->parseProcessingMode($request->request->get('processingMode')),
                strategy: $this->parseStrategy($request->request->get('strategy')),
            ));
        } catch (InvalidSourceException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(
            UploadAudioResponse::fromResult($result)->toArray(),
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

        return ProcessingStrategy::tryFrom($value) ?? null;
    }
}
