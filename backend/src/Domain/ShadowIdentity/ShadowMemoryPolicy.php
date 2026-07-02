<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

final readonly class ShadowMemoryPolicy
{
    /**
     * @param list<string> $knownSkills
     * @param list<string> $unknownSkills
     * @param list<string> $goals
     * @param list<string> $interests
     */
    public function __construct(
        private bool $rememberPreferences,
        private bool $rememberConversationContext,
        private array $knownSkills = [],
        private array $unknownSkills = [],
        private array $goals = [],
        private array $interests = [],
    ) {
    }

    public static function default(): self
    {
        return new self(
            rememberPreferences: true,
            rememberConversationContext: true,
        );
    }

    public function rememberPreferences(): bool
    {
        return $this->rememberPreferences;
    }

    public function rememberConversationContext(): bool
    {
        return $this->rememberConversationContext;
    }

    /**
     * @return list<string>
     */
    public function knownSkills(): array
    {
        return $this->knownSkills;
    }

    /**
     * @return list<string>
     */
    public function unknownSkills(): array
    {
        return $this->unknownSkills;
    }

    /**
     * @return list<string>
     */
    public function goals(): array
    {
        return $this->goals;
    }

    /**
     * @return list<string>
     */
    public function interests(): array
    {
        return $this->interests;
    }

    public function withInterest(string $interest): self
    {
        if (in_array($interest, $this->interests, true)) {
            return $this;
        }

        return new self(
            $this->rememberPreferences,
            $this->rememberConversationContext,
            $this->knownSkills,
            $this->unknownSkills,
            $this->goals,
            [...$this->interests, $interest],
        );
    }

    public function forgetPreference(string $key): self
    {
        return new self(
            $this->rememberPreferences,
            $this->rememberConversationContext,
            array_values(array_filter($this->knownSkills, static fn (string $s): bool => $s !== $key)),
            array_values(array_filter($this->unknownSkills, static fn (string $s): bool => $s !== $key)),
            array_values(array_filter($this->goals, static fn (string $s): bool => $s !== $key)),
            array_values(array_filter($this->interests, static fn (string $s): bool => $s !== $key)),
        );
    }

    public function cleared(): self
    {
        return new self(
            $this->rememberPreferences,
            $this->rememberConversationContext,
        );
    }
}
