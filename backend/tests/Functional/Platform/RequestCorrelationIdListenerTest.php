<?php

declare(strict_types=1);

namespace App\Tests\Functional\Platform;

use App\Infrastructure\Platform\RequestContextProvider;
use App\Infrastructure\Platform\RequestCorrelationIdListener;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RequestCorrelationIdListenerTest extends WebTestCase
{
    public function testReusesIncomingCorrelationIdHeader(): void
    {
        $client = static::createClient();
        $correlationId = 'c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d';

        $client->request(
            'GET',
            '/health',
            server: [sprintf('HTTP_%s', str_replace('-', '_', RequestCorrelationIdListener::HEADER_NAME)) => $correlationId],
        );

        self::assertResponseIsSuccessful();

        $request = $client->getRequest();
        self::assertNotNull($request);
        self::assertTrue($request->attributes->has(RequestContextProvider::ATTRIBUTE_KEY));
        self::assertSame(
            $correlationId,
            $request->attributes->get(RequestContextProvider::ATTRIBUTE_KEY)->value,
        );
    }

    public function testGeneratesCorrelationIdWhenHeaderIsMissing(): void
    {
        $client = static::createClient();

        $client->request('GET', '/health');

        self::assertResponseIsSuccessful();

        $request = $client->getRequest();
        self::assertNotNull($request);
        self::assertTrue($request->attributes->has(RequestContextProvider::ATTRIBUTE_KEY));
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $request->attributes->get(RequestContextProvider::ATTRIBUTE_KEY)->value,
        );
    }
}
