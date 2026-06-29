<?php

declare(strict_types=1);

namespace App\Infrastructure\Agent;

use App\Domain\Agent\ConversationMemoryExecution;
use App\Domain\Agent\ConversationMemoryResult;
use App\Domain\Agent\ConversationMemoryToolExecutorInterface;

final class NullConversationMemoryToolExecutor implements ConversationMemoryToolExecutorInterface
{
    public function execute(ConversationMemoryExecution $execution): ConversationMemoryResult
    {
        return ConversationMemoryResult::empty();
    }
}
