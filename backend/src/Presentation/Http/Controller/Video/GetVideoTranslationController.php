<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Translation\Handlers\GetVideoTranslationHandler;
use App\Application\Translation\Queries\GetVideoTranslationQuery;
use App\Domain\Translation\Exception\InvalidTranslationException;
use App\Presentation\Http\Response\Translation\VideoTranslationResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoTranslationController extends AbstractController
{
    #[Route('/api/videos/{videoId}/translations/{language}', name: 'api_videos_translation_get', methods: ['GET'])]
    public function __invoke(string $videoId, string $language, GetVideoTranslationHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetVideoTranslationQuery($videoId, $language));
        } catch (InvalidTranslationException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(VideoTranslationResponse::fromResult($result)->toArray());
    }
}
