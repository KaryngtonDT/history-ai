<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LayerDependencyRulesTest extends TestCase
{
    public function testRuleHelperDetectsForbiddenImports(): void
    {
        $tempDirectory = sys_get_temp_dir() . '/history-ai-architecture-' . uniqid('', true);
        mkdir($tempDirectory);

        $filePath = $tempDirectory . '/Example.php';
        file_put_contents(
            $filePath,
            <<<'PHP'
            <?php

            declare(strict_types=1);

            namespace App\Domain\Example;

            use Doctrine\ORM\EntityManager;

            final class Example
            {
            }
            PHP,
        );

        $violations = LayerDependencyRules::findViolations(
            $tempDirectory,
            ['Doctrine\\'],
        );

        unlink($filePath);
        rmdir($tempDirectory);

        self::assertCount(1, $violations);
        self::assertStringContainsString('Doctrine\\ORM\\EntityManager', $violations[0]);
    }
}
