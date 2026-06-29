<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Platform\Support;

use App\Application\Platform\RequestContext;
use App\Application\Platform\RequestContextProviderInterface;
use App\Domain\Platform\CorrelationId;

final class FixedRequestContextProvider implements RequestContextProviderInterface
{
    public function __construct(
        private readonly CorrelationId $correlationId,
    ) {
    }

    public function getContext(): RequestContext
    {
        return new RequestContext($this->correlationId);
    }
}
