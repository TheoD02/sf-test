<?php

use Castor\Attribute\AsListener;
use Castor\Attribute\AsTask;
use Castor\Event\AfterExecuteTaskEvent;
use Castor\Event\BeforeExecuteTaskEvent;
use Symfony\Component\Process\ExecutableFinder;
use function Castor\capture;
use function Castor\context;
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
        // docker compose
        root_context()->workingDirectory . '/compose.yaml',
        root_context()->workingDirectory . '/compose.override.yaml',
    ];

    io()->section("Replacing placeholders in project files");
    foreach ($files as $file) {
        if (fs()->exists($file) === false) {
            continue;
        }
        $contents = file_get_contents($file);
        $contents = str_replace('<app-name-placeholder>', $appName, $contents);
        file_put_contents($file, $contents);
    }
    io()->success('Done!');

    $isDeployWithDockerImage = io()->confirm('Do you want to deploy with a Docker image?');
    if ($isDeployWithDockerImage) {
        io()->newLine();
        do {
            $registryDomain = io()->ask('What is the registry domain?', $registryDomain ?? 'docker.io');
            $registryNamespace = io()->ask('What is the registry namespace?', $registryNamespace ?? 'theod');
            $dockerImage = io()->ask('What is the Docker image?', $dockerImage ?? $appName);
        } while (io()->confirm("Is this correct? {$registryDomain}/{$registryNamespace}/{$dockerImage}", false) === false);

        $replace = [
            '<docker-registry-placeholder>',
            '<docker-namespace-placeholder>',
            '<docker-image-placeholder>',
        ];
        $replaceWith = [
            $registryDomain,
            $registryNamespace,
            $dockerImage,
        ];

        io()->note('Replacing placeholders in infra.php');
        $contents = file_get_contents(root_context()->workingDirectory . '/.castor/src/infra.php');
        foreach ($replace as $index => $value) {
            $contents = str_replace($value, $replaceWith[$index], $contents);
        }
        file_put_contents(root_context()->workingDirectory . '/.castor/src/infra.php', $contents);
        io()->success('Done!');
    } else {
        fs()->remove(root_context()->workingDirectory . '/.castor/src/infra.php');
    }

    start();
    io()->newLine();

    $cleanSymfony = io()->confirm('Do you want a clean symfony installation?', false);
    if ($cleanSymfony) {
        foreach (finder()->in(app_context()->workingDirectory)->directories()->depth(0) as $directory) {
            fs()->remove($directory);
        }
        foreach (finder()->in(app_context()->workingDirectory)->ignoreDotFiles(false)->files()->depth(0) as $file) {
            fs()->remove($file);
        }
        symfony_installation();
    }

    io()->success('Project setup complete');
    io()->info([
        'Main commands:',
        '  castor start - Start the project',
        '  castor install - Install dependencies (auto starts the project, installs dependencies and resets the database)',
        '  castor shell - Open a shell in the project',
        '  castor db:reset - Reset the database',
        $isDeployWithDockerImage ? '  castor deploy - Deploy the project' : '',
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

        $version = io()->choice('Choose Symfony version', $versions, 'Latest Stable');
        $version = $mapping[$version];
        io()->note('Creating project with symfony/skeleton in temp directory');
        composer()->add('create-project', "symfony/skeleton:{$version} sf-temp")->run(context()->withQuiet());

        $tempDestination = "{$destination}/sf-temp";
        io()->note('Copying files to the destination directory.');
        fs()->mirror($tempDestination, $destination);

        io()->note('Removing temporary directory.');
        fs()->remove($tempDestination);

        io()->note('Adding frankenphp-symfony to composer.json and setting minimum php version to 8.3');
        composer()->add('require', '\"php:>=8.3\"', 'runtime/frankenphp-symfony')->run(context()->withQuiet());

        io()->note('Adding symfony.docker to composer.json for docker support');
        composer()->add('config', '--json', 'extra.symfony.docker', 'true')->run(context()->withQuiet());
    } else {
        io()->newLine();
        io()->warning('A composer.json file was found. This should not happen. Please retry.');

        exit(1);
    }
}