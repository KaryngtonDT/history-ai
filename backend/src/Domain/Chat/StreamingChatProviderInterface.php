<?php

declare(strict_types=1);

namespace App\Domain\Chat;

interface StreamingChatProviderInterface
{
    public function stream(ChatRequest $request): ChatStream;
}
