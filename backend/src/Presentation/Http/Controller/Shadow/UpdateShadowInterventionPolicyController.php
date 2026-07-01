<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\Commands\UpdateShadowInterventionPolicyCommand;
use App\Application\Shadow\Handlers\UpdateShadowInterventionPolicyHandler;
use App\Application\Shadow\ShadowInterventionPolicyMapper;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Presentation\Http\Response\Shadow\ShadowInterventionPolicyResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateShadowInterventionPolicyController extends AbstractController
{
    #[OA\Put(
        operationId: 'updateShadowInterventionPolicy',
        summary: 'Update proactive Shadow tutor policy for a session',
        tags: ['Shadow'],
        responses: [
            new OA\Response(response: 200, description: 'Updated policy'),
            new OA\Response(response: 400, description: 'Invalid request'),
        ],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/sessions/{sessionId}/policy',
        name: 'api_videos_shadow_sessions_policy_update',
        methods: ['PUT'],
    )]
    public function __invoke(
        string $videoId,
        string $sessionId,
        Request $request,
        ShadowSessionResolver $sessionResolver,
        ShadowInterventionPolicyMapper $policyMapper,
        UpdateShadowInterventionPolicyHandler $handler,
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
            $session = $sessionResolver->resolve($videoId, $sessionId);
            $policy = $policyMapper->fromArray($payload, $session->interventionPolicy());
            $result = $handler(new UpdateShadowInterventionPolicyCommand(
                videoId: $videoId,
                sessionId: $sessionId,
                policy: $policy,
            ));
        } catch (InvalidShadowSessionException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(ShadowInterventionPolicyResponse::fromResult($result)->toArray());
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
