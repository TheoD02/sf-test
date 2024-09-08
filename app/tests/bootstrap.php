<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0o000);
}

try {
    (new Filesystem())->remove(__DIR__ . '/../var/cache/test');
} catch (Throwable) { // @phpstan-ignore-line
    echo 'Warning: Cache directory is not empty, please clear it manually!'; // @phpstan-ignore-line
}
