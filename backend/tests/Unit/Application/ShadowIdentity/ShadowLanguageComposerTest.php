<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowIdentity;

use App\Application\ShadowIdentity\ShadowLanguageComposer;
use App\Domain\ShadowIdentity\ShadowIdentityPreferences;
use App\Domain\ShadowIdentity\ShadowLanguageProfile;
use App\Domain\ShadowIdentity\ShadowTechnicalLanguagePolicy;
use PHPUnit\Framework\TestCase;

final class ShadowLanguageComposerTest extends TestCase
{
    private ShadowLanguageComposer $composer;

    protected function setUp(): void
    {
        $this->composer = new ShadowLanguageComposer();
    }

    public function testComposesMixedLanguageInstructions(): void
    {
        $profile = ShadowLanguageProfile::default()
            ->withPrimaryLanguage('fr')
            ->withTechnicalTermsPolicy(ShadowTechnicalLanguagePolicy::AlwaysOriginal);

        $instructions = $this->composer->composeInstructions(
            ShadowIdentityPreferences::default()->withLanguageProfile($profile),
        );

        self::assertStringContainsString('Primary explanation language: fr.', $instructions[0]);
        self::assertTrue(
            (bool) array_filter(
                $instructions,
                static fn (string $line): bool => str_contains($line, 'Keep technical terms in their original language.'),
            ),
        );
    }

    public function testOralCommandUpdatesPrimaryAndTechnicalLanguages(): void
    {
        $result = $this->composer->applyOralCommand(
            ShadowLanguageProfile::default(),
            'Explique en français. Conserve les termes techniques anglais. Le résumé est en allemand.',
        );

        self::assertSame('fr', $result['profile']->primaryLanguage());
        self::assertSame('en', $result['profile']->technicalLanguage());
        self::assertSame('de', $result['profile']->summaryLanguage());
        self::assertNotEmpty($result['applied']);
    }
}
