<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\Commands\AnswerShadowInterventionCommand;
use App\Application\Shadow\Handlers\AnswerShadowInterventionHandler;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Presentation\Http\Request\Shadow\AnswerShadowInterventionRequest;
use App\Presentation\Http\Request\Shadow\Exception\InvalidShadowRequestException;
use App\Presentation\Http\Response\Shadow\ShadowInterventionAnswerResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AnswerShadowInterventionController extends AbstractController
{
    #[OA\Post(
        operationId: 'answerShadowIntervention',
        summary: 'Answer a proactive Shadow intervention',
        tags: ['Shadow'],
        responses: [
            new OA\Response(response: 200, description: 'Shadow reply'),
            new OA\Response(response: 400, description: 'Invalid request'),
        ],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/sessions/{sessionId}/intervention/{interventionId}/answer',
        name: 'api_videos_shadow_sessions_intervention_answer',
        methods: ['POST'],
    )]
    public function __invoke(
        string $videoId,
        string $sessionId,
        string $interventionId,
        Request $request,
        AnswerShadowInterventionHandler $handler,
    ): JsonResponse {
        try {
            new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            return $this->invalidRequestResponse();
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->invalidRequestResponse();
        }

        try {
            $answerRequest = AnswerShadowInterventionRequest::fromArray($payload);
        } catch (InvalidShadowRequestException) {
            return $this->invalidRequestResponse();
        }

        try {
            $result = $handler(new AnswerShadowInterventionCommand(
                videoId: $videoId,
                sessionId: $sessionId,
                interventionId: $interventionId,
                answer: $answerRequest->answer,
                currentTimeSeconds: $answerRequest->time,
            ));
        } catch (InvalidShadowSessionException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(ShadowInterventionAnswerResponse::fromResult($result)->toArray());
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
