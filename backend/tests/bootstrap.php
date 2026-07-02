<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
putenv('APP_ENV=test');

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env', 'test');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

$testCacheDir = dirname(__DIR__).'/var/cache/test';

foreach (['storage', 'models'] as $subdir) {
    $path = $testCacheDir.'/'.$subdir;

    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}
