<?php

declare(strict_types=1);

namespace App\Infrastructure\Platform;

use App\Application\Platform\PlatformLoggerInterface;
use App\Application\Platform\RequestContextProviderInterface;
use Psr\Log\LoggerInterface;

final class PlatformLogger implements PlatformLoggerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly RequestContextProviderInterface $requestContextProvider,
    ) {
    }

    public function info(string $component, string $message, array $context = []): void
    {
        $this->logger->info($message, $this->buildContext($component, $context));
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    private function buildContext(string $component, array $context): array
    {
        return array_merge($context, [
            'correlationId' => $this->requestContextProvider->getContext()->correlationId->value,
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'component' => $component,
        ]);
    }
}
