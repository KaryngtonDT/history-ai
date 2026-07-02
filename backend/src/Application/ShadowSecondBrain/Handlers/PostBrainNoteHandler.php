<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain\Handlers;

use App\Application\ShadowSecondBrain\WorkspaceBuilder;

final class PostBrainNoteHandler
{
    public function __construct(
        private readonly WorkspaceBuilder $builder,
    ) {
    }

    /** @param array<string, mixed> $payload */
    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $beforeCount = count($this->builder->getWorkspace($scopeKey)->notes()->all());
        $workspace = $this->builder->addNote($scopeKey, $payload);
        $notes = $workspace->notes()->all();
        $created = $notes[$beforeCount] ?? $notes[array_key_last($notes)];

        return [
            'scopeKey' => $scopeKey,
            'note' => [
                'id' => $created->id(),
                'body' => $created->body(),
                'createdAt' => $created->createdAt()->format(\DateTimeInterface::ATOM),
                'conceptKey' => $created->conceptKey(),
            ],
        ];
    }
}
