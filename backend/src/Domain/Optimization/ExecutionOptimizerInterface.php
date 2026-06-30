<?php

declare(strict_types=1);

namespace App\Domain\Optimization;

use App\Domain\VideoIntelligence\VideoIntelligence;

interface ExecutionOptimizerInterface
{
    public function optimize(VideoIntelligence $intelligence): ExecutionOptimization;
}
