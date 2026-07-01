<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\YouTube;

use App\Application\YouTube\Handlers\PreviewYouTubeHandler;
use App\Application\YouTube\Queries\PreviewYouTubeQuery;
use App\Domain\YouTube\Exception\InvalidYouTubeException;
use App\Presentation\Http\Response\YouTube\PreviewYouTubeResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PreviewYouTubeController extends AbstractController
{
    #[OA\Post(
        operationId: 'previewYouTube',
        summary: 'Preview YouTube metadata',
        tags: ['YouTube'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['url'],
                properties: [
                    new OA\Property(property: 'url', type: 'string'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Metadata preview'),
            new OA\Response(response: 400, description: 'Invalid URL'),
        ],
    )]
    #[Route('/api/youtube/preview', name: 'api_youtube_preview', methods: ['POST'])]
    public function __invoke(Request $request, PreviewYouTubeHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload) || !is_string($payload['url'] ?? null)) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $handler(new PreviewYouTubeQuery(trim($payload['url'])));
        } catch (InvalidYouTubeException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(PreviewYouTubeResponse::fromResult($result)->toArray());
    }
}
