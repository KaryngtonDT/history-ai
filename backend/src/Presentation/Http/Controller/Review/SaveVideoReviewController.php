<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Review;

use App\Application\Review\Commands\SaveReviewCommand;
use App\Application\Review\SaveReviewHandler;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Review\Exception\InvalidReviewException;
use App\Presentation\Http\CollaboratorResolver;
use App\Domain\Review\ReviewCategory;
use App\Domain\Video\VideoId;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SaveVideoReviewController extends AbstractController
{
    #[OA\Post(
        operationId: 'saveVideoReview',
        summary: 'Save a review for a video',
        tags: ['Review'],
        parameters: [
            new OA\Parameter(name: 'videoId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SaveReviewRequest'),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Review saved', content: new OA\JsonContent(ref: '#/components/schemas/Review')),
            new OA\Response(response: 400, description: 'Invalid review', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    #[Route('/api/videos/{videoId}/reviews', name: 'api_videos_reviews_save', methods: ['POST'])]
    public function __invoke(string $videoId, Request $request, SaveReviewHandler $handler): JsonResponse
    {
        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid request body'], Response::HTTP_BAD_REQUEST);
        }

        $scoresInput = is_array($payload['scores'] ?? null) ? $payload['scores'] : [];
        $scores = [];

        foreach (ReviewCategory::cases() as $category) {
            if (!isset($scoresInput[$category->value]) || !is_numeric($scoresInput[$category->value])) {
                return $this->json(
                    ['error' => sprintf('Missing or invalid score for "%s".', $category->value)],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            $scores[$category->value] = (int) $scoresInput[$category->value];
        }

        $executionVersion = isset($payload['executionVersionNumber'])
            ? (int) $payload['executionVersionNumber']
            : 1;
        $comment = is_string($payload['comment'] ?? null) ? $payload['comment'] : '';

        try {
            $collaborator = CollaboratorResolver::fromRequest($request);
            $result = $handler(new SaveReviewCommand(
                new VideoId($videoId),
                max(1, $executionVersion),
                $scores,
                $comment,
                $collaborator->userId,
            ));
        } catch (InvalidWorkspaceMemberException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (InvalidReviewException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(ReviewResponseFactory::fromResult($result), Response::HTTP_CREATED);
    }
}
