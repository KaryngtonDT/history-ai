<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\YouTube;

use App\Application\YouTube\Handlers\ListYouTubeHandler;
use App\Application\YouTube\Queries\ListYouTubeQuery;
use App\Presentation\Http\Response\YouTube\GetYouTubeResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListYouTubeController extends AbstractController
{
    #[OA\Get(
        operationId: 'listYouTubeImports',
        summary: 'List YouTube imports',
        tags: ['YouTube'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recent YouTube imports',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/YouTubeImportResponse'),
                ),
            ),
        ],
    )]
    #[Route('/api/youtube', name: 'api_youtube_list', methods: ['GET'])]
    public function __invoke(ListYouTubeHandler $handler): JsonResponse
    {
        $results = $handler(new ListYouTubeQuery());

        return $this->json(array_map(
            static fn ($result) => GetYouTubeResponse::fromResult($result)->toArray(),
            $results,
        ));
    }
}
