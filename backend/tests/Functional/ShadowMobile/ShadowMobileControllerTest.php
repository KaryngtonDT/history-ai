<?php

declare(strict_types=1);

namespace App\Tests\Functional\ShadowMobile;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ShadowMobileControllerTest extends WebTestCase
{
    private const string SCOPE = 'mobile-functional-test';

    public function testProfileReturnsDefaultMobileWorkspace(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/shadow/mobile/profile?scopeKey='.self::SCOPE);

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertSame(self::SCOPE, $payload['scopeKey'] ?? null);
        self::assertSame('disconnected', $payload['state'] ?? null);
        self::assertSame('auto', $payload['connection']['mode'] ?? null);
    }

    public function testDeviceRegisterCreatesRegisteredDevice(): void
    {
        $client = static::createClient();
        $scope = self::SCOPE.'-device';

        $client->request(
            'POST',
            '/api/shadow/mobile/device',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'scopeKey' => $scope,
                'deviceId' => 'test-device-001',
                'platform' => 'android',
                'name' => 'Test Phone',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertSame('test-device-001', $payload['device']['deviceId'] ?? null);
        self::assertSame('android', $payload['device']['platform'] ?? null);
        self::assertSame($scope, $payload['scopeKey'] ?? null);
    }

    public function testConnectionUpdatePersistsTailscaleMode(): void
    {
        $client = static::createClient();
        $scope = self::SCOPE.'-connection';

        $client->request(
            'PUT',
            '/api/shadow/mobile/connection',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'scopeKey' => $scope,
                'mode' => 'tailscale',
                'tailscaleUrl' => 'http://lumen.tailnet:8080',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertSame('tailscale', $payload['connection']['mode'] ?? null);
        self::assertSame('http://lumen.tailnet:8080', $payload['connection']['tailscaleUrl'] ?? null);

        $client->request('GET', '/api/shadow/mobile/connections?scopeKey='.$scope);
        self::assertResponseIsSuccessful();
        $connections = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('tailscale', $connections['connection']['mode'] ?? null);
    }

    public function testHealthReturnsPlatformChecks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/shadow/mobile/health?scopeKey='.self::SCOPE.'-health');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertArrayHasKey('status', $payload);
        self::assertArrayHasKey('checks', $payload);
        self::assertArrayHasKey('connectionMode', $payload);
    }
}
