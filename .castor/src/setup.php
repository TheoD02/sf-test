<?php

use Castor\Attribute\AsListener;
use Castor\Attribute\AsTask;
use Castor\Event\AfterExecuteTaskEvent;
use Castor\Event\BeforeExecuteTaskEvent;
use Symfony\Component\Process\ExecutableFinder;
use function Castor\capture;
use function Castor\finder;
use function Castor\fs;
use function Castor\http_request;
use function Castor\io;
use function Symfony\Component\String\u;
use function TheoD\MusicAutoTagger\app_context;
use function TheoD\MusicAutoTagger\root_context;
use function TheoD\MusicAutoTagger\Runner\composer;

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

    $cleanSymfony = io()->confirm('Do you want a clean symfony installation?');
    if ($cleanSymfony) {
        fs()->remove(app_context()->workingDirectory);
        fs()->mkdir(app_context()->workingDirectory);
        symfony_installation();
    }

    io()->success('Project setup complete');
    io()->info([
        'You can now run `castor start` to start the project',
        '',
        "You can access the app at https://{$appName}.web.localhost after running `castor start`",
    ]);

    //unlink(__FILE__);
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
    } else {
        io()->writeln('<info> OK </info>');
    }

    io()->write('Checking if traefik container is running...');
    $output = capture('docker ps');

    if (str_contains($output, 'traefik') === false) {
        io()->writeln('<error> KO </error>');
        io()->error('Traefik container is not running. Please start it before running this command.');
    } else {
        io()->writeln('<info> OK </info>');
    }

    io()->success('All requirements are met');
}

function symfony_installation(): void
{
    $destination = app_context()->workingDirectory;
    if (is_file("{$destination}/composer.json") === false) {
        $response = http_request('GET', 'https://symfony.com/releases.json')->toArray();
        $versions = [
            substr($response['symfony_versions']['stable'], 0, 3) => 'Latest Stable',
            substr($response['symfony_versions']['lts'], 0, 3) => 'Latest LTS',
            substr($response['symfony_versions']['next'], 0, 3) => 'Next',
        ];
        $mapping = [
            substr($response['symfony_versions']['stable'], 0, 3) => substr(
                    $response['symfony_versions']['stable'],
                    0,
                    3,
                ) . '.*',
            substr($response['symfony_versions']['lts'], 0, 3) => substr(
                    $response['symfony_versions']['lts'],
                    0,
                    3,
                ) . '.*',
            substr($response['symfony_versions']['next'], 0, 3) => substr(
                    $response['symfony_versions']['next'],
                    0,
                    3,
                ) . '.*-dev',
        ];

        $diff = array_diff($response['maintained_versions'], array_keys($versions));

        foreach ($diff as $version) {
            $versions[$version] = "{$version} Maintained";
            $mapping[$version] = $version . '.*';
        }

        ksort($versions);

        io()->newLine();
        io()->warning('Symfony seems not to be installed.');

        if (io()->confirm('Do you want to install it now?') === false) {
            return;
        }

        $version = io()->choice('Choose Symfony version', $versions, 'Latest Stable');
        $version = $mapping[$version];
        composer()->add('create-project', "symfony/skeleton:{$version} sf-temp")->run();

        $tempDestination = "{$destination}/sf-temp";
        io()->newLine();
        io()->note('Copying files to the destination directory.');
        fs()->mirror($tempDestination, $destination);

        io()->note('Removing temporary directory.');
        fs()->remove($tempDestination);
    }
}