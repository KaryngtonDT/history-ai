<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\ShadowIdentity;

use App\Application\ShadowIdentity\ShadowIdentityBehaviorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetShadowIdentitySuggestionsController extends AbstractController
{
    #[Route('/api/shadow/identity/suggestions', name: 'api_shadow_identity_suggestions', methods: ['GET'])]
    public function __invoke(Request $request, ShadowIdentityBehaviorResolver $resolver): JsonResponse
    {
        $scopeKey = is_string($request->query->get('scopeKey')) ? $request->query->get('scopeKey') : 'default';
        $contentCategory = is_string($request->query->get('contentCategory'))
            ? $request->query->get('contentCategory')
            : null;

        return $this->json($resolver->adaptiveContext($scopeKey, $contentCategory));
    }
}
