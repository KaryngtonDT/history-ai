<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowIdentity;

use App\Application\ShadowIdentity\Handlers\PostShadowIdentityResetHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PostShadowIdentityResetController extends AbstractController
{
    #[Route('/api/shadow/identity/reset', name: 'api_shadow_identity_reset', methods: ['POST'])]
    public function __invoke(Request $request, PostShadowIdentityResetHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $scopeKey = is_array($payload) && is_string($payload['scopeKey'] ?? null)
            ? $payload['scopeKey']
            : (is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default');

        return $this->json($handler($scopeKey));
    }
}
