<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\ShadowIdentitySnapshot;
use App\Domain\ShadowIdentity\ShadowIdentitySnapshotCollection;
use App\Domain\ShadowIdentity\ShadowIdentityPreferences;
use App\Domain\ShadowIdentity\ShadowTechnicalLanguagePolicy;
use App\Domain\ShadowIdentity\ShadowLanguageProfile;
use PHPUnit\Framework\TestCase;

final class ShadowIdentitySnapshotTest extends TestCase
{
    public function testCapturesSnapshotWithPreferences(): void
    {
        $snapshot = ShadowIdentitySnapshot::capture(
            ShadowIdentityPreferences::default(),
            'Voice changed',
            'conversational',
        );

        self::assertSame('Voice changed', $snapshot->label());
        self::assertSame('conversational', $snapshot->source());
    }

    public function testCollectionAppendsSnapshots(): void
    {
        $collection = ShadowIdentitySnapshotCollection::empty();
        $first = ShadowIdentitySnapshot::capture(ShadowIdentityPreferences::default(), 'First');
        $second = ShadowIdentitySnapshot::capture(ShadowIdentityPreferences::default(), 'Second');

        $updated = $collection->append($first)->append($second);

        self::assertSame(0, $collection->count());
        self::assertSame(2, $updated->count());
        self::assertSame('Second', $updated->latest()?->label());
    }

    public function testLanguageProfileSupportsTechnicalTermsPolicy(): void
    {
        $profile = ShadowLanguageProfile::default()->withTechnicalTermsPolicy(
            ShadowTechnicalLanguagePolicy::AlwaysOriginal,
        );

        self::assertSame(
            ShadowTechnicalLanguagePolicy::AlwaysOriginal,
            $profile->technicalTermsPolicy(),
        );
    }
}
