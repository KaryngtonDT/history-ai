<?php

declare(strict_types=1);

namespace App\Infrastructure\Platform;

use App\Application\Platform\RequestContext;
use App\Application\Platform\RequestContextProviderInterface;
use App\Domain\Platform\CorrelationId;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestContextProvider implements RequestContextProviderInterface
{
    public const string ATTRIBUTE_KEY = '_correlation_id';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getContext(): RequestContext
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request && $request->attributes->has(self::ATTRIBUTE_KEY)) {
            /** @var CorrelationId $correlationId */
            $correlationId = $request->attributes->get(self::ATTRIBUTE_KEY);

            return new RequestContext($correlationId);
        }

        return new RequestContext(CorrelationId::generate());
    }
}
