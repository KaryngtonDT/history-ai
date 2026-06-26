<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Minimal CORS for local frontend (localhost:5173) → Symfony API.
 * Dev only — replace with a dedicated CORS policy before production.
 */
final class CorsSubscriber implements EventSubscriberInterface
{
    private const ALLOWED_ORIGIN = 'http://localhost:5173';

    public function __construct(
        #[Autowire('%kernel.environment%')]
        private readonly string $appEnv,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9999],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->appEnv !== 'dev' || !$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->getMethod() !== 'OPTIONS' || !$this->isApiPath($request->getPathInfo())) {
            return;
        }

        if ($request->headers->get('Origin') !== self::ALLOWED_ORIGIN) {
            return;
        }

        $response = new Response('', Response::HTTP_NO_CONTENT);
        $this->addCorsHeaders($response);

        $event->setResponse($response);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->appEnv !== 'dev' || !$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->isApiPath($request->getPathInfo())) {
            return;
        }

        if ($request->headers->get('Origin') !== self::ALLOWED_ORIGIN) {
            return;
        }

        $this->addCorsHeaders($event->getResponse());
    }

    private function isApiPath(string $path): bool
    {
        return str_starts_with($path, '/api');
    }

    private function addCorsHeaders(Response $response): void
    {
        $response->headers->set('Access-Control-Allow-Origin', self::ALLOWED_ORIGIN);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept');
    }
}
