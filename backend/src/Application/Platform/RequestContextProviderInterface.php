<?php

declare(strict_types=1);

namespace App\Application\Platform;

interface RequestContextProviderInterface
{
    public function getContext(): RequestContext;
}
