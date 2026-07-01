<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Shadow;

use App\Application\Shadow\Commands\UpdateShadowVoicePreferenceCommand;
use App\Application\Shadow\Handlers\UpdateShadowVoicePreferenceHandler;
use App\Application\Shadow\ShadowSessionResolver;
use App\Application\Shadow\ShadowVoicePreferenceMapper;
use App\Domain\Shadow\Exception\InvalidShadowSessionException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Presentation\Http\Response\Shadow\ShadowVoicePreferenceResponse;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateShadowVoicePreferenceController extends AbstractController
{
    #[OA\Put(
        operationId: 'updateShadowVoicePreference',
        summary: 'Update Shadow voice language preference for a session',
        tags: ['Shadow'],
        responses: [
            new OA\Response(response: 200, description: 'Updated voice preference'),
            new OA\Response(response: 400, description: 'Invalid request'),
        ],
    )]
    #[Route(
        '/api/videos/{videoId}/shadow/sessions/{sessionId}/voice',
        name: 'api_videos_shadow_sessions_voice_update',
        methods: ['PUT'],
    )]
    public function __invoke(
        string $videoId,
        string $sessionId,
        Request $request,
        ShadowSessionResolver $sessionResolver,
        ShadowVoicePreferenceMapper $voicePreferenceMapper,
        UpdateShadowVoicePreferenceHandler $handler,
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
            $voicePreference = $voicePreferenceMapper->fromArray(
                $payload,
                $session->voicePreference(),
            );
            $result = $handler(new UpdateShadowVoicePreferenceCommand(
                videoId: $videoId,
                sessionId: $sessionId,
                voicePreference: $voicePreference,
            ));
        } catch (InvalidShadowSessionException) {
            return $this->invalidRequestResponse();
        }

        return $this->json(ShadowVoicePreferenceResponse::fromResult($result)->toArray());
    }

    private function invalidRequestResponse(): JsonResponse
    {
        return $this->json(['error' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
    }
}
