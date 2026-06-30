<?php

declare(strict_types=1);

namespace App\Tests\Functional\AI;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ListAIProvidersControllerTest extends WebTestCase
{
    public function testListsRegisteredProviders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/ai/providers');

        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('engines', $payload);
        self::assertNotSame([], $payload['engines']);

        $capabilities = array_map(
            static fn (array $engine): string => $engine['capability'],
            $payload['engines'],
        );

        self::assertContains('speech_to_text', $capabilities);
        self::assertContains('translation', $capabilities);
        self::assertContains('text_to_speech', $capabilities);
    }
}
