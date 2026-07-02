<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Learning;

use App\Application\Learning\LearningProfileJsonMapper;
use App\Domain\Learning\LearningProfile;
use App\Infrastructure\Learning\FileLearningProfileRepository;
use App\Infrastructure\Storage\JsonFileStore;
use PHPUnit\Framework\TestCase;

final class FileLearningProfileRepositoryTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/lumen-learning-' . uniqid('', true);
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
        $mapper = new LearningProfileJsonMapper();
        $profile = LearningProfile::create(scopeKey: 'default');

        $repository = new FileLearningProfileRepository(new JsonFileStore($this->directory), $mapper);
        $repository->save($profile);

        $reloaded = new FileLearningProfileRepository(new JsonFileStore($this->directory), $mapper);
        $found = $reloaded->findByScope('default');

        self::assertNotNull($found);
        self::assertSame($profile->id()->value, $found->id()->value);
        self::assertSame('default', $found->scopeKey());
    }
}
