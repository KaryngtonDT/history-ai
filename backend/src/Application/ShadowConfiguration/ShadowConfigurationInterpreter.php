<?php

declare(strict_types=1);

namespace App\Application\ShadowConfiguration;

use App\Application\ShadowIdentity\ShadowIdentityJsonMapper;
use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Domain\ShadowIdentity\ShadowIdentityRepositoryInterface;

final class ShadowConfigurationInterpreter
{
    public function __construct(
        private readonly ShadowConfigurationIntentDetector $detector,
        private readonly ShadowConfigurationExecutor $executor,
        private readonly ShadowConfigurationConfirmation $confirmation,
        private readonly ShadowIdentityRepositoryInterface $identityRepository,
        private readonly ShadowIdentityJsonMapper $mapper,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function interpret(string $utterance, string $scopeKey = 'default', bool $confirmed = false): array
    {
        $identity = $this->resolveIdentity($scopeKey);
        $detection = $this->detector->detect($utterance);
        $preview = $this->executor->previewChange($identity->preferences(), $detection);
        $requiresConfirmation = $this->confirmation->requiresConfirmation($detection->intent);

        if (
            ShadowConfigurationIntent::Unknown !== $detection->intent
            && ($confirmed || !$requiresConfirmation)
        ) {
            $updated = $this->executor->apply($identity, $detection);
            $this->identityRepository->save($updated);
            $identity = $updated;
        }

        return [
            'intent' => $detection->intent->value,
            'confidence' => $detection->confidence,
            'explanation' => $detection->explanation,
            'preview' => $preview,
            'requiresConfirmation' => $requiresConfirmation && !$confirmed,
            'confirmationMessage' => $this->confirmation->build($detection, $preview, $confirmed || !$requiresConfirmation),
            'applied' => ShadowConfigurationIntent::Unknown !== $detection->intent
                && ($confirmed || !$requiresConfirmation),
            'profile' => $this->mapper->toArray($identity),
        ];
    }

    private function resolveIdentity(string $scopeKey): ShadowIdentity
    {
        $existing = $this->identityRepository->findByScope($scopeKey);

        if (null !== $existing) {
            return $existing;
        }

        $identity = ShadowIdentity::create(scopeKey: $scopeKey);
        $this->identityRepository->save($identity);

        return $identity;
    }
}
