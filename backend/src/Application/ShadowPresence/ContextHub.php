<?php

declare(strict_types=1);

namespace App\Application\ShadowPresence;

use App\Application\ShadowExecutive\ExecutiveCoordinator;
use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Application\ShadowMemory\MemoryBuilder;
use App\Application\ShadowMentor\MentorBuilder;
use App\Application\ShadowSecondBrain\WorkspaceBuilder;
use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Domain\ShadowIdentity\ShadowIdentityRepositoryInterface;
use App\Domain\ShadowPresence\PresenceContext;
use App\Domain\ShadowPresence\PresenceSurface;

final class ContextHub
{
    public function __construct(
        private readonly WorkspaceBuilder $workspaceBuilder,
        private readonly MemoryBuilder $memoryBuilder,
        private readonly MentorBuilder $mentorBuilder,
        private readonly ExecutiveCoordinator $executiveCoordinator,
        private readonly KnowledgeBuilder $knowledgeBuilder,
        private readonly ShadowIdentityRepositoryInterface $identityRepository,
        private readonly ConversationBridge $conversationBridge,
    ) {
    }

    public function build(string $scopeKey, PresenceSurface $surface, ?string $shadowSessionId = null): PresenceContext
    {
        $explainability = [];

        $identityLabel = $this->resolveIdentityLabel($scopeKey, $explainability);
        $conceptCount = $this->resolveConceptCount($scopeKey, $explainability);
        $activeMissionTitle = $this->resolveActiveMissionTitle($scopeKey, $explainability);
        $executiveHint = $this->resolveExecutiveHint($scopeKey, $explainability);
        $conversationSessionId = $this->resolveConversationSessionId($scopeKey, $shadowSessionId, $explainability);

        return new PresenceContext(
            $scopeKey,
            $surface,
            $identityLabel,
            $conceptCount,
            $activeMissionTitle,
            $executiveHint,
            $conversationSessionId,
            $explainability,
        );
    }

    /** @param list<string> $explainability */
    private function resolveIdentityLabel(string $scopeKey, array &$explainability): string
    {
        try {
            $identity = $this->identityRepository->findByScope($scopeKey) ?? ShadowIdentity::create(scopeKey: $scopeKey);
            $explainability[] = 'identity:persona';

            return $identity->preferences()->persona()->value;
        } catch (\Throwable) {
            return 'teacher';
        }
    }

    /** @param list<string> $explainability */
    private function resolveConceptCount(string $scopeKey, array &$explainability): int
    {
        try {
            $graph = $this->knowledgeBuilder->syncGraph($scopeKey);
            $explainability[] = 'knowledge:graph';

            return count($graph->nodes()->all());
        } catch (\Throwable) {
            try {
                $workspace = $this->workspaceBuilder->getWorkspace($scopeKey);
                $explainability[] = 'brain:workspace';

                return count($workspace->entries()->all());
            } catch (\Throwable) {
                return 0;
            }
        }
    }

    /** @param list<string> $explainability */
    private function resolveActiveMissionTitle(string $scopeKey, array &$explainability): ?string
    {
        try {
            $plan = $this->mentorBuilder->syncPlan($scopeKey);
            $mission = $plan->currentMission();
            $explainability[] = 'mentor:mission';

            return $mission?->title();
        } catch (\Throwable) {
            return null;
        }
    }

    /** @param list<string> $explainability */
    private function resolveExecutiveHint(string $scopeKey, array &$explainability): ?string
    {
        try {
            $plan = $this->executiveCoordinator->syncPlan($scopeKey);
            $recommendations = $plan->recommendations()->all();
            $explainability[] = 'executive:recommendations';

            if ([] === $recommendations) {
                return null;
            }

            return $recommendations[0]->title();
        } catch (\Throwable) {
            return null;
        }
    }

    /** @param list<string> $explainability */
    private function resolveConversationSessionId(
        string $scopeKey,
        ?string $shadowSessionId,
        array &$explainability,
    ): ?string {
        if (null === $shadowSessionId) {
            return null;
        }

        try {
            $this->memoryBuilder->ingestRelationship($scopeKey);
            $explainability[] = 'memory:timeline';

            return $this->conversationBridge->resolveConversationSessionId($shadowSessionId);
        } catch (\Throwable) {
            return null;
        }
    }
}

