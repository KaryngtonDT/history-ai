<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowIdentity;

use App\Application\ShadowConfiguration\ShadowConfigurationInterpreter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PostShadowConfigurationController extends AbstractController
{
    #[Route('/api/shadow/identity/configure', name: 'api_shadow_identity_configure', methods: ['POST'])]
    public function __invoke(Request $request, ShadowConfigurationInterpreter $interpreter): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        $utterance = is_string($payload['utterance'] ?? null) ? trim($payload['utterance']) : '';

        if ('' === $utterance) {
            return $this->json(['error' => 'Utterance is required.'], 400);
        }

        $scopeKey = is_string($payload['scopeKey'] ?? null) ? $payload['scopeKey'] : 'default';
        $confirmed = true === ($payload['confirmed'] ?? false);

        return $this->json($interpreter->interpret($utterance, $scopeKey, $confirmed));
    }
}
