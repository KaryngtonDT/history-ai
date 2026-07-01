<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Audio;

use App\Application\AudioUpload\Handlers\ListAudioHandler;
use App\Application\AudioUpload\Queries\ListAudioQuery;
use App\Presentation\Http\Response\Audio\GetAudioResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListAudioController extends AbstractController
{
    #[OA\Get(
        operationId: 'listAudio',
        summary: 'List audio sources',
        description: 'Returns recently uploaded audio sources.',
        tags: ['Audio'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Audio sources',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/AudioSourceResponse'),
                ),
            ),
        ],
    )]
    #[Route('/api/audio', name: 'api_audio_list', methods: ['GET'])]
    public function __invoke(ListAudioHandler $handler): JsonResponse
    {
        $results = $handler(new ListAudioQuery());

        return $this->json(array_map(
            static fn ($result) => GetAudioResponse::fromResult($result)->toArray(),
            $results,
        ));
    }
}
