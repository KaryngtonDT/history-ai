<?php

declare(strict_types=1);

namespace App\Application\Shadow\Handlers;

use App\Application\Shadow\Commands\UpdateShadowVoicePreferenceCommand;
use App\Application\Shadow\DTO\ShadowVoicePreferenceResult;
use App\Application\Shadow\ShadowSessionResolver;
use App\Domain\Shadow\ShadowSessionRepositoryInterface;

final class UpdateShadowVoicePreferenceHandler
{
    public function __construct(
        private readonly ShadowSessionRepositoryInterface $sessionRepository,
        private readonly ShadowSessionResolver $sessionResolver,
    ) {
    }

    public function __invoke(UpdateShadowVoicePreferenceCommand $command): ShadowVoicePreferenceResult
    {
        $session = $this->sessionResolver->resolve($command->videoId, $command->sessionId);
        $session = $session->withVoicePreference($command->voicePreference);

        $this->sessionRepository->save($session);

        return ShadowVoicePreferenceResult::fromDomain($session->voicePreference());
    }
}
