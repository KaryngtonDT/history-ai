<?php

declare(strict_types=1);

namespace App\Domain\Scheduler;

use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\VideoIntelligence\VideoIntelligence;

interface PipelineSchedulerInterface
{
    public function schedule(
        VideoIntelligence $intelligence,
        ExecutionOptimization $optimization,
    ): ExecutionSchedule;
}
