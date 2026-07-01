<?php

declare(strict_types=1);

namespace App\Presentation\Http;

use App\Application\Collaboration\CollaboratorContext;
use Symfony\Component\HttpFoundation\Request;

final class CollaboratorResolver
{
    public static function fromRequest(Request $request): CollaboratorContext
    {
        $userId = $request->headers->get('X-Collaborator-Id');
        $displayName = $request->headers->get('X-Collaborator-Name');

        return new CollaboratorContext(
            is_string($userId) && '' !== trim($userId)
                ? trim($userId)
                : CollaboratorContext::DEFAULT_USER_ID,
            is_string($displayName) && '' !== trim($displayName)
                ? trim($displayName)
                : CollaboratorContext::DEFAULT_DISPLAY_NAME,
        );
    }
}
