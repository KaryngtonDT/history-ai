<?php

declare(strict_types=1);

namespace App\Application\Platform;

use App\Domain\Platform\CorrelationId;

final readonly class RequestContext
{
    public function __construct(
        public CorrelationId $correlationId,
    ) {
    }
}
