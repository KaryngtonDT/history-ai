<?php

declare(strict_types=1);

namespace App\Infrastructure\Processing;

use App\Application\Processing\Ports\ProcessingOrchestratorInterface;
use App\Domain\Processing\ProcessingJob;

final class NoOpProcessingOrchestrator implements ProcessingOrchestratorInterface
{
    public function dispatch(ProcessingJob $job): void
    {
    }
}
