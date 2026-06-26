<?php

declare(strict_types=1);

namespace App\Application\Processing\Ports;

use App\Domain\Processing\ProcessingJob;

interface ProcessingOrchestratorInterface
{
    public function dispatch(ProcessingJob $job): void;
}
