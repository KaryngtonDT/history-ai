<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Shadow;

use App\Application\Shadow\DTO\ShadowInterventionPolicyResult;

final readonly class ShadowInterventionPolicyResponse
{
    public function __construct(public array $policy)
    {
    }

    public static function fromResult(ShadowInterventionPolicyResult $result): self
    {
        return new self($result->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['policy' => $this->policy];
    }
}
