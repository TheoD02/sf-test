<?php

use Castor\Attribute\AsListener;
use Castor\Attribute\AsTask;
use Castor\Event\BeforeExecuteTaskEvent;
use Symfony\Component\Process\ExecutableFinder;
use function Castor\capture;
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

    io()->section("Setting up project with name {$appName}");
    foreach ($files as $file) {
        $contents = file_get_contents($file);
        $contents = str_replace('<app-name-placeholder>', $appName, $contents);
        file_put_contents($file, $contents);
    }

    io()->success('Project setup complete');
    io()->info([
        'You can now run `castor start` to start the project',
        '',
        "You can access the app at https://{$appName}.web.localhost after running `castor start`",
    ]);

    unlink(__FILE__);
}

#[AsListener(BeforeExecuteTaskEvent::class, priority: PHP_INT_MAX)]
function check_tool_deps(BeforeExecuteTaskEvent $event): void
{
    io()->write('Checking if docker is installed...');
    if ((new ExecutableFinder())->find('docker') === null) {
        io()->writeln('<error> KO </error>');
        io()->error(
            [
                'Docker is required for running this application',
                'Check documentation: https://docs.docker.com/engine/install',
            ],
        );
        exit(1);
    } else {
        io()->writeln('<info> OK </info>');
    }

    io()->write('Checking if traefik container is running...');
    $output = capture('docker ps');

    if (str_contains($output, 'traefik') === false) {
        io()->writeln('<error> KO </error>');
        io()->error('Traefik container is not running. Please start it before running this command.');
        exit(1);
    } else {
        io()->writeln('<info> OK </info>');
    }
}