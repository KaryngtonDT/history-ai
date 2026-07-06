<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Provisioning;

use App\Application\Runtime\EngineCompatibilityEvaluator;
use App\Domain\Hardware\HardwareProvider;
use App\Domain\Engine\EngineRepositoryInterface;
use App\Infrastructure\Hardware\HardwareReportStore;

final class ProvisioningCompatibilityGate
{
    public function __construct(
        private readonly HardwareReportStore $hardwareReportStore,
        private readonly EngineRepositoryInterface $engineRepository,
        private readonly EngineCompatibilityEvaluator $compatibilityEvaluator,
    ) {
    }

    public function isHardwareBlocked(string $engineId): bool
    {
        if (!$this->hardwareReportStore->has()) {
            return false;
        }

        $report = $this->hardwareReportStore->get();
        if (null === $report) {
            return false;
        }

        $engine = $this->engineRepository->findById($engineId);
        if (null === $engine) {
            return false;
        }

        $result = $this->compatibilityEvaluator->evaluate(
            $engine,
            $report->profile->type,
            $report->capabilities,
            HardwareProvider::Host,
        );

        return !$result->hardwareCompatible;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function blockedPayload(string $engineId): ?array
    {
        if (!$this->isHardwareBlocked($engineId)) {
            return null;
        }

        $report = $this->hardwareReportStore->require();
        $engine = $this->engineRepository->findById($engineId);
        if (null === $engine) {
            return null;
        }

        $result = $this->compatibilityEvaluator->evaluate(
            $engine,
            $report->profile->type,
            $report->capabilities,
            HardwareProvider::Host,
        );

        return [
            'engineId' => $engineId,
            'status' => 'blocked',
            'provisioned' => false,
            'ok' => false,
            'installAttempted' => false,
            'blockedReason' => $result->humanReason,
            'blockedReasonCode' => $result->blockedReasonCode->value,
            'missingRequirements' => $result->missingRequirements,
            'recommendedAlternative' => $result->recommendedAlternative,
            'compatibleProviders' => array_values(array_filter([
                $result->canBeFixedByRemoteProvider ? 'remote' : null,
                $result->canBeFixedByHardware ? 'host' : null,
            ])),
        ];
    }
}
