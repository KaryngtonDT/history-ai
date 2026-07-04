<?php

declare(strict_types=1);

namespace App\Application\ShadowBrowser\Handlers;

use App\Application\ShadowBrowser\BrowserActionDispatcher;
use App\Domain\ShadowBrowser\BrowserActionType;

final class PostBrowserActionHandler
{
    public function __construct(
        private readonly BrowserActionDispatcher $dispatcher,
    ) {
    }

    /** @param array<string, mixed> $payload */
    public function __invoke(string $scopeKey, BrowserActionType $action, array $payload): array
    {
        return $this->dispatcher->dispatch($scopeKey, $action, $payload);
    }
}
