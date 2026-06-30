<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Application\Workspace\Commands\ProcessProjectCommand;
use App\Application\Workspace\Commands\RunBatchProcessingCommand;
use App\Application\Workspace\DTO\RunBatchProcessingResult;
use App\Application\Workspace\RunBatchProcessingHandler;

final class ProcessProjectHandler
{
    public function __construct(
        private readonly RunBatchProcessingHandler $runBatchProcessingHandler,
    ) {
    }

    public function __invoke(ProcessProjectCommand $command): RunBatchProcessingResult
    {
        return ($this->runBatchProcessingHandler)(new RunBatchProcessingCommand(
            $command->projectId,
            $command->targetLanguages,
            $command->processingMode,
            $command->strategy,
        ));
    }
}
