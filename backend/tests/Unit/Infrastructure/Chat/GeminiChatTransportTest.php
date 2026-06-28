<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Chat;

use App\Infrastructure\Chat\CurlGeminiChatTransport;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class GeminiChatTransportTest extends TestCase
{
    public function testRequiresConfiguredApiKeyBeforeNetworkCall(): void
    {
        $transport = new CurlGeminiChatTransport('');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GEMINI_API_KEY is not configured.');

        $transport->generateContent([
            'model' => 'gemini-2.5-flash',
            'contents' => [],
        ]);
    }

    public function testRejectsMissingModelInPayload(): void
    {
        $transport = new CurlGeminiChatTransport('test-api-key');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('requires a model');

        $transport->generateContent([
            'contents' => [],
        ]);
    }

    public function testRejectsBlankApiKeyBeforeNetworkCall(): void
    {
        $transport = new CurlGeminiChatTransport('   ');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('GEMINI_API_KEY is not configured.');

        $transport->generateContent([
            'model' => 'gemini-2.5-flash',
            'contents' => [],
        ]);
    }
}
