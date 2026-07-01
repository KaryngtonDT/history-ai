<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Review;

use App\Application\Review\GetReviewHandler;
use App\Application\Review\Queries\GetReviewsQuery;
use App\Presentation\Http\CollaboratorResolver;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoReviewsController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoReviews',
        summary: 'Get reviews for a video',
        tags: ['Review'],
        parameters: [
            new OA\Parameter(name: 'videoId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Video reviews',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Review')),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/reviews', name: 'api_videos_reviews_list', methods: ['GET'])]
    public function __invoke(string $videoId, Request $request, GetReviewHandler $handler): JsonResponse
    {
        $collaborator = CollaboratorResolver::fromRequest($request);
        $reviews = $handler(new GetReviewsQuery($videoId, $collaborator->userId));

        return $this->json(array_map(
            static fn ($review): array => ReviewResponseFactory::fromResult($review),
            $reviews,
        ));
    }
}
