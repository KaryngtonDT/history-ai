<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Audio;

use App\Application\AudioUpload\Handlers\GetAudioHandler;
use App\Application\AudioUpload\Queries\GetAudioQuery;
use App\Domain\Source\Exception\InvalidSourceException;
use App\Presentation\Http\Response\Audio\GetAudioResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetAudioController extends AbstractController
{
    #[OA\Get(
        operationId: 'getAudio',
        summary: 'Get audio source',
        description: 'Returns metadata for an uploaded audio source.',
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
            new OA\Response(
                response: 200,
                description: 'Audio source metadata',
                content: new OA\JsonContent(ref: '#/components/schemas/AudioSourceResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Audio not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/audio/{audioId}', name: 'api_audio_get', methods: ['GET'])]
    public function __invoke(string $audioId, GetAudioHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetAudioQuery($audioId));
        } catch (InvalidSourceException) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(GetAudioResponse::fromResult($result)->toArray());
    }
}
