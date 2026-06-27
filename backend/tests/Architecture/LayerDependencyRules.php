<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class LayerDependencyRules
{
    /**
     * @return list<string>
     */
    public static function collectPhpFiles(string $rootDirectory): array
    {
        if (!is_dir($rootDirectory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootDirectory, FilesystemIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files);

        return $files;
    }

    /**
     * @return list<string>
     */
    public static function extractUseStatements(string $filePath): array
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            return [];
        }

        preg_match_all('/^use\s+([^;]+);/m', $content, $matches);

        return array_map(static fn (string $statement): string => trim($statement), $matches[1]);
    }

    /**
     * @param list<string> $forbiddenPrefixes
     *
     * @return list<string>
     */
    public static function findViolations(
        string $layerDirectory,
        array $forbiddenPrefixes,
    ): array {
        $violations = [];

        foreach (self::collectPhpFiles($layerDirectory) as $filePath) {
            foreach (self::extractUseStatements($filePath) as $useStatement) {
                foreach ($forbiddenPrefixes as $prefix) {
                    if (!str_starts_with($useStatement, $prefix)) {
                        continue;
                    }

                    $relativePath = self::relativePath($filePath);
                    $violations[] = sprintf('%s imports forbidden %s', $relativePath, $useStatement);
                }
            }
        }

        sort($violations);

        return $violations;
    }

    private static function relativePath(string $absolutePath): string
    {
        $projectRoot = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;

        return str_replace('\\', '/', str_replace($projectRoot, '', $absolutePath));
    }
}
