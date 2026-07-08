<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

use App\Domain\Engine\EngineCatalogCapability;

final readonly class RuntimeResolveRequest
{
    public function __construct(
        public EngineCatalogCapability $capability,
        public RuntimeResolveContext $context = new RuntimeResolveContext(),
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $capability = EngineCatalogCapability::from((string) ($data['capability'] ?? 'speech_to_text'));
        $contextData = is_array($data['context'] ?? null) ? $data['context'] : $data;

        return new self(
            capability: $capability,
            context: RuntimeResolveContext::fromArray($contextData),
        );
    }
}
