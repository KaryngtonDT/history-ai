<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Translation\Handlers\ListVideoTranslationsHandler;
use App\Application\Translation\Queries\ListVideoTranslationsQuery;
use App\Domain\Translation\Exception\InvalidTranslationException;
use App\Presentation\Http\Response\Translation\VideoTranslationSummaryResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListVideoTranslationsController extends AbstractController
{
    #[Route('/api/videos/{videoId}/translations', name: 'api_videos_translations_list', methods: ['GET'])]
    public function __invoke(string $videoId, ListVideoTranslationsHandler $handler): JsonResponse
    {
        try {
            $summaries = $handler(new ListVideoTranslationsQuery($videoId));
        } catch (InvalidTranslationException) {
            return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'videoId' => $videoId,
            'translations' => array_map(
                static fn ($summary): array => VideoTranslationSummaryResponse::fromSummary($summary)->toArray(),
                $summaries,
            ),
        ]);
    }
}
