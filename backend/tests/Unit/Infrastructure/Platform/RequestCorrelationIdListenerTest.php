<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Platform;

use App\Domain\Platform\CorrelationId;
use App\Infrastructure\Platform\RequestContextProvider;
use App\Infrastructure\Platform\RequestCorrelationIdListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class RequestCorrelationIdListenerTest extends TestCase
{
    private RequestCorrelationIdListener $listener;

    protected function setUp(): void
    {
        $this->listener = new RequestCorrelationIdListener();
    }

    public function testReusesValidCorrelationIdHeader(): void
    {
        $correlationId = 'c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d';
        $request = Request::create('/api/health');
        $request->headers->set(RequestCorrelationIdListener::HEADER_NAME, $correlationId);
        $event = new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        /** @var CorrelationId $stored */
        $stored = $request->attributes->get(RequestContextProvider::ATTRIBUTE_KEY);

        self::assertInstanceOf(CorrelationId::class, $stored);
        self::assertSame($correlationId, $stored->value);
    }

    public function testGeneratesCorrelationIdWhenHeaderIsMissing(): void
    {
        $request = Request::create('/api/health');
        $event = new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        /** @var CorrelationId $stored */
        $stored = $request->attributes->get(RequestContextProvider::ATTRIBUTE_KEY);

        self::assertInstanceOf(CorrelationId::class, $stored);
        self::assertTrue(CorrelationId::isValid($stored->value));
    }

    public function testGeneratesCorrelationIdWhenHeaderIsInvalid(): void
    {
        $request = Request::create('/api/health');
        $request->headers->set(RequestCorrelationIdListener::HEADER_NAME, 'invalid');
        $event = new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);

        $this->listener->onKernelRequest($event);

        /** @var CorrelationId $stored */
        $stored = $request->attributes->get(RequestContextProvider::ATTRIBUTE_KEY);

        self::assertInstanceOf(CorrelationId::class, $stored);
        self::assertTrue(CorrelationId::isValid($stored->value));
        self::assertNotSame('invalid', $stored->value);
    }

    public function testIgnoresSubRequests(): void
    {
        $request = Request::create('/api/health');
        $event = new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::SUB_REQUEST);

        $this->listener->onKernelRequest($event);

        self::assertFalse($request->attributes->has(RequestContextProvider::ATTRIBUTE_KEY));
    }
}
