<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\ShadowIdentity;

use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Infrastructure\ShadowIdentity\FileShadowIdentityRepository;
use App\Infrastructure\ShadowIdentity\ShadowIdentityPersistenceMapper;
use App\Infrastructure\Storage\JsonFileStore;
use PHPUnit\Framework\TestCase;

final class FileShadowIdentityRepositoryTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/lumen-shadow-id-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->directory)) {
            array_map('unlink', glob($this->directory . '/*.json') ?: []);
            rmdir($this->directory);
        }
    }

    public function testSaveAndFindByScopePersistsAcrossInstances(): void
    {
        $mapper = new ShadowIdentityPersistenceMapper();
        $identity = ShadowIdentity::create(scopeKey: 'default');

        $repository = new FileShadowIdentityRepository(new JsonFileStore($this->directory), $mapper);
        $repository->save($identity);

        $reloaded = new FileShadowIdentityRepository(new JsonFileStore($this->directory), $mapper);
        $found = $reloaded->findByScope('default');

        self::assertNotNull($found);
        self::assertSame($identity->id()->value, $found->id()->value);
        self::assertSame('default', $found->scopeKey());
    }
}
