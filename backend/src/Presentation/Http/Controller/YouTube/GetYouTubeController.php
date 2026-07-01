<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\YouTube;

use App\Application\YouTube\Handlers\GetYouTubeHandler;
use App\Application\YouTube\Queries\GetYouTubeQuery;
use App\Domain\YouTube\Exception\InvalidYouTubeException;
use App\Presentation\Http\Response\YouTube\GetYouTubeResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetYouTubeController extends AbstractController
{
    #[OA\Get(
        operationId: 'getYouTubeImport',
        summary: 'Get YouTube import',
        tags: ['YouTube'],
        parameters: [
            new OA\Parameter(
                name: 'youtubeId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'YouTube import details',
                content: new OA\JsonContent(ref: '#/components/schemas/YouTubeImportResponse'),
            ),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    #[Route('/api/youtube/{youtubeId}', name: 'api_youtube_get', methods: ['GET'])]
    public function __invoke(string $youtubeId, GetYouTubeHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetYouTubeQuery($youtubeId));
        } catch (InvalidYouTubeException) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(GetYouTubeResponse::fromResult($result)->toArray());
    }
}
