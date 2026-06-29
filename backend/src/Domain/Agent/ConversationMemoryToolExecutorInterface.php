<?php

declare(strict_types=1);

namespace App\Domain\Agent;

interface ConversationMemoryToolExecutorInterface
{
    public function execute(ConversationMemoryExecution $execution): ConversationMemoryResult;
}
