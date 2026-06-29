<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Platform\Support;

use App\Application\Platform\PlatformLoggerInterface;
use App\Application\Platform\RequestContextProviderInterface;

final class RecordingPlatformLogger implements PlatformLoggerInterface
{
    /** @var list<array{component: string, message: string, context: array<string, mixed>}> */
    private array $records = [];

    public function __construct(
        private readonly RequestContextProviderInterface $requestContextProvider,
    ) {
    }

    public function info(string $component, string $message, array $context = []): void
    {
        $this->records[] = [
            'component' => $component,
            'message' => $message,
            'context' => array_merge($context, [
                'correlationId' => $this->requestContextProvider->getContext()->correlationId->value,
            ]),
        ];
    }

    /**
     * @return list<array{component: string, message: string, context: array<string, mixed>}>
     */
    public function records(): array
    {
        return $this->records;
    }

    /**
     * @return list<string>
     */
    public function messages(): array
    {
        return array_map(
            static fn (array $record): string => $record['message'],
            $this->records,
        );
    }
}
