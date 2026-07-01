<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Audio;

use App\Application\AudioUpload\Commands\DeleteAudioCommand;
use App\Application\AudioUpload\Handlers\DeleteAudioHandler;
use App\Domain\Source\Exception\InvalidSourceException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteAudioController extends AbstractController
{
    #[OA\Delete(
        operationId: 'deleteAudio',
        summary: 'Delete audio source',
        description: 'Deletes an audio source record and its stored file.',
        tags: ['Audio'],
        parameters: [
            new OA\Parameter(
                name: 'audioId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Audio deleted'),
            new OA\Response(
                response: 404,
                description: 'Audio not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/audio/{audioId}', name: 'api_audio_delete', methods: ['DELETE'])]
    public function __invoke(string $audioId, DeleteAudioHandler $handler): JsonResponse
    {
        try {
            $handler(new DeleteAudioCommand($audioId));
        } catch (InvalidSourceException) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
