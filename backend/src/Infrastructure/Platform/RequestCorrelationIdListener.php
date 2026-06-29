<?php

declare(strict_types=1);

namespace App\Infrastructure\Platform;

use App\Application\Platform\RequestContextProviderInterface;
use App\Domain\Platform\CorrelationId;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestCorrelationIdListener implements EventSubscriberInterface
{
    public const string HEADER_NAME = 'X-Correlation-ID';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $headerValue = $request->headers->get(self::HEADER_NAME);

        $correlationId = null !== $headerValue && CorrelationId::isValid($headerValue)
            ? new CorrelationId($headerValue)
            : CorrelationId::generate();

        $request->attributes->set(RequestContextProvider::ATTRIBUTE_KEY, $correlationId);
    }
}
