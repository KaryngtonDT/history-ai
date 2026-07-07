<?php

declare(strict_types=1);

/**
 * Replace createMock() with createStub() when the double never uses expects().
 */

$root = dirname(__DIR__).'/tests';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$changedFiles = 0;

foreach ($iterator as $file) {
    if (!$file->isFile() || 'php' !== $file->getExtension()) {
        continue;
    }

    $path = $file->getPathname();
    $content = file_get_contents($path);

    if (!is_string($content) || !str_contains($content, 'createMock(')) {
        continue;
    }

    $original = $content;

    if (preg_match_all('/(\$this->\w+|\$\w+)\s*=\s*\$this->createMock\(/', $content, $matches)) {
        foreach (array_unique($matches[1]) as $identifier) {
            if (preg_match('/'.preg_quote($identifier, '/').'\s*->\s*expects\s*\(/s', $content)) {
                continue;
            }

            $content = preg_replace(
                '/'.preg_quote($identifier, '/').'\s*=\s*\$this->createMock\(/',
                $identifier.' = $this->createStub(',
                $content,
            ) ?? $content;
        }
    }

    $lines = explode("\n", $content);
    foreach ($lines as $index => $line) {
        if (!str_contains($line, 'createMock(')) {
            continue;
        }

        if (preg_match('/=\s*\$this->createMock\(/', $line)) {
            continue;
        }

        $lines[$index] = str_replace('$this->createMock(', '$this->createStub(', $line);
    }

    $content = implode("\n", $lines);

    if ($content !== $original) {
        file_put_contents($path, $content);
        ++$changedFiles;
    }
}

echo "Updated {$changedFiles} file(s).".PHP_EOL;
