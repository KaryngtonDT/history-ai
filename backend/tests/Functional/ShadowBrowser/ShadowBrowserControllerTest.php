<?php

declare(strict_types=1);

namespace App\Tests\Functional\ShadowBrowser;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ShadowBrowserControllerTest extends WebTestCase
{
    private const string SCOPE = 'browser-functional-test';

    public function testConnectCreatesActiveBrowserSession(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/shadow/browser/connect',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['scopeKey' => self::SCOPE], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertTrue($payload['session']['active'] ?? false);
        self::assertSame('connected', $payload['session']['session']['state'] ?? null);
        self::assertSame(self::SCOPE, $payload['scopeKey'] ?? null);
    }

    public function testPlatformDetectsYoutube(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/shadow/browser/platform',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'scopeKey' => self::SCOPE.'-platform',
                'url' => 'https://www.youtube.com/watch?v=test123',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertSame('youtube', $payload['platform'] ?? null);
        self::assertSame('youtube.com', $payload['host'] ?? null);
    }

    public function testPermissionsReturnDefaultsAndAcceptUpdates(): void
    {
        $client = static::createClient();
        $scope = self::SCOPE.'-permissions';

        $client->request('GET', '/api/shadow/browser/permissions?scopeKey='.$scope);
        self::assertResponseIsSuccessful();
        $defaults = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($defaults);
        self::assertArrayHasKey('defaults', $defaults);
        self::assertTrue($defaults['defaults']['allowed'] ?? false);

        $client->request(
            'PUT',
            '/api/shadow/browser/permissions',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'scopeKey' => $scope,
                'sitePolicies' => [
                    [
                        'host' => 'youtube.com',
                        'allowed' => true,
                        'permissions' => [
                            ['permission' => 'read_page_context', 'granted' => true],
                            ['permission' => 'read_selection', 'granted' => true],
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $updated = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($updated);
        self::assertCount(1, $updated['sitePolicies'] ?? []);
        self::assertSame('youtube.com', $updated['sitePolicies'][0]['host'] ?? null);

        $granted = array_column($updated['sitePolicies'][0]['permissions'] ?? [], 'granted', 'permission');
        self::assertTrue($granted['read_page_context'] ?? false);
        self::assertTrue($granted['read_selection'] ?? false);
    }
}
