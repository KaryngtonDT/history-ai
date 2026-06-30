<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Video;

use App\Application\Quality\Handlers\GetQualityReportHandler;
use App\Application\Quality\Queries\GetQualityReportQuery;
use App\Domain\Video\Exception\InvalidVideoJobException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVideoQualityController extends AbstractController
{
    #[OA\Get(
        operationId: 'getVideoQuality',
        summary: 'Get quality assessment report for a video',
        description: 'Returns AI quality assessment scores and publication recommendation for a video.',
        tags: ['Quality'],
        parameters: [
            new OA\Parameter(
                name: 'videoId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Quality report',
                content: new OA\JsonContent(ref: '#/components/schemas/QualityReport'),
            ),
            new OA\Response(
                response: 404,
                description: 'Video not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    #[Route('/api/videos/{videoId}/quality', name: 'api_videos_quality_get', methods: ['GET'])]
    public function __invoke(string $videoId, GetQualityReportHandler $handler): JsonResponse
    {
        try {
            $result = $handler(new GetQualityReportQuery($videoId));
        } catch (InvalidVideoJobException) {
            return $this->json(['error' => 'Video not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $result->id,
            'videoId' => $result->videoId,
            'overallScore' => $result->overallScore,
            'recommendation' => $result->recommendation,
            'metrics' => $result->metrics,
            'explanations' => $result->explanations,
        ]);
    }
}
