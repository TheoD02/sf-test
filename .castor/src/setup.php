<?php

use Castor\Attribute\AsTask;
use function Castor\finder;
use function Castor\io;
use function Symfony\Component\String\u;
use function TheoD\MusicAutoTagger\root_context;

#[AsTask]
function setup(): void
{
    $defaultAppName = u(basename(dirname(__DIR__, 2)))->snake()->replace('_', '-')->toString();
    $appName = io()->ask('What is the name of the app?', $defaultAppName);

    // replace <app-name-placeholder> with $appName
    $files = [
        // castor
        root_context()->workingDirectory . '/.castor/src/ContainerDefinitionBag.php',
        root_context()->workingDirectory . '/.castor/src/listeners.php',
        root_context()->workingDirectory . '/.castor/castor.php',
        // api
        root_context()->workingDirectory . '/api/environments/Local.bru',
        root_context()->workingDirectory . '/api/environments/Remote.bru',
        // app
        root_context()->workingDirectory . '/app/.env',
        root_context()->workingDirectory . '/app/vite.config.js',
    ];

    foreach ($files as $file) {
        $contents = file_get_contents($file);
        $contents = str_replace('<app-name-placeholder>', $appName, $contents);
        file_put_contents($file, $contents);
    }
}