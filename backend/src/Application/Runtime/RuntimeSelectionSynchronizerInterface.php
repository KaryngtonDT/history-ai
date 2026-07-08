<?php

declare(strict_types=1);

namespace App\Application\Runtime;

use App\Domain\Pipeline\PipelineConfiguration;

interface RuntimeSelectionSynchronizerInterface
{
    public function syncFromPipelineConfiguration(?PipelineConfiguration $configuration = null): void;
}
