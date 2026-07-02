<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive\Handlers;

use App\Application\ShadowExecutive\ExecutiveCoordinator;
use App\Application\ShadowExecutive\ExecutiveJsonMapper;
use App\Domain\ShadowExecutive\Exception\InvalidShadowExecutiveException;

final class PostDeferDecisionHandler
{
    public function __construct(
        private readonly ExecutiveCoordinator $coordinator,
        private readonly ExecutiveJsonMapper $mapper,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, string $decisionId): array
    {
        try {
            $plan = $this->coordinator->deferDecision($scopeKey, $decisionId);
        } catch (InvalidShadowExecutiveException $exception) {
            return ['error' => $exception->getMessage()];
        }

        $decision = $plan->findDecision($decisionId);

        if (null === $decision) {
            return ['error' => 'Executive decision not found.'];
        }

        return [
            'scopeKey' => $scopeKey,
            'decision' => $this->mapper->decisionToArray($decision),
        ];
    }
}
