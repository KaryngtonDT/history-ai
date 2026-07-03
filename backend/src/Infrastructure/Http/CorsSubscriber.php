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
 * Minimal CORS for local frontends → Symfony API.
 * Dev only — replace with a dedicated CORS policy before production.
 */
final class CorsSubscriber implements EventSubscriberInterface
{
    /** @var list<string> */
    private const ALLOWED_ORIGINS = [
        'http://localhost:5173',
        'http://localhost:1420',
    ];

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
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $origin = $request->headers->get('Origin');

        if ($request->getMethod() !== 'OPTIONS' || !$this->isApiPath($request->getPathInfo())) {
            return;
        }

        if (!$this->shouldApplyCors($origin)) {
            return;
        }

        $response = new Response('', Response::HTTP_NO_CONTENT);
        $this->addCorsHeaders($response, $origin);

        $event->setResponse($response);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $origin = $request->headers->get('Origin');

        if (!$this->isApiPath($request->getPathInfo())) {
            return;
        }

        if (!$this->shouldApplyCors($origin)) {
            return;
        }

        $this->addCorsHeaders($event->getResponse(), $origin);
    }

    private function shouldApplyCors(?string $origin): bool
    {
        if (!$this->isAllowedOrigin($origin)) {
            return false;
        }

        if ('dev' === $this->appEnv) {
            return true;
        }

        return is_string($origin)
            && (str_starts_with($origin, 'http://localhost:')
                || str_starts_with($origin, 'http://127.0.0.1:'));
    }

    private function isAllowedOrigin(?string $origin): bool
    {
        if (!is_string($origin) || '' === $origin) {
            return false;
        }

        if (in_array($origin, self::ALLOWED_ORIGINS, true)) {
            return true;
        }

        return str_starts_with($origin, 'http://localhost:')
            || str_starts_with($origin, 'http://127.0.0.1:');
    }

    private function isApiPath(string $path): bool
    {
        return str_starts_with($path, '/api');
    }

    private function addCorsHeaders(Response $response, ?string $origin): void
    {
        if (!is_string($origin) || !$this->isAllowedOrigin($origin)) {
            return;
        }

        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept');
    }
}
