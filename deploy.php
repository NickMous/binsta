<?php

namespace Deployer;

use Deployer\Exception\Exception;

require 'contrib/npm.php';
require 'contrib/sentry.php';
require 'recipe/deploy/check_remote.php';
require 'recipe/deploy/cleanup.php';
require 'recipe/laravel.php';

set('repository', 'git@github.com:NickMous/binsta.git');
set('dotenv-local', __DIR__.'/.env');
//set('bin/bun', function () {
//    return run('which bun');
//});
set('branch', function () {
    return runLocally('git rev-parse --abbrev-ref HEAD');
});
set('git_recursive', false);

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts
foreach (range(1, 2) as $i) {
    host('binsta0'.$i)
        ->setHostname('83.87.185.201')
        ->setRemoteUser('deployer')
        ->setPort(1124)
        ->setDeployPath('/var/www/binsta/0'.$i);
}

host('production')
    ->setHostname('83.87.185.201')
    ->setRemoteUser('deployer')
    ->setPort(1124)
    ->setDeployPath('/var/www/binsta/prod');

task('git:check-local-changes', function () {
    $result = runLocally('git status --porcelain');

    if (! empty($result)) {
        throw new Exception('Local changes detected! Commit your changes before deploying.');
    }

    $localBranch = runLocally('git rev-parse --abbrev-ref HEAD');
    $remoteBranch = runLocally('git rev-parse --abbrev-ref @{u}');
    $result = runLocally("git rev-list --left-right --count $localBranch...$remoteBranch");
    [$ahead, $behind] = explode("\t", $result);

    if ($ahead > 0) {
        throw new Exception('Local branch is ahead of the remote branch! Push your changes before deploying.');
    }

    if ($behind > 0) {
        throw new Exception('Local branch is behind the remote branch! Pull the changes before deploying.');
    }
});

task('local:copy-env', function () {
    if (file_exists('.env')) {
        runLocally('cp .env .env.backup');
        set('env_backup', true);
        download('{{deploy_path}}/shared/.env', '.env');
    } else {
        writeln('<comment>.env file not found on server</comment>');
        set('env_backup', false);
    }
});

task('local:restore-env', function () {
    if (get('env_backup')) {
        runLocally('mv .env.backup .env');
    }
});

//task('deploy:bun:install', function () {
//    run('cd {{release_path}} && ~/.bun/bin/bun i');
//});
//
//task('deploy:bun:build', function () {
//    run('cd {{release_path}} && ~/.bun/bin/bun run build');
//});

task('dotenv:load', function () {
    $dotenv = get('dotenv-local');
    if (! file_exists($dotenv)) {
        throw new Exception('.env file not found');
    }

    $content = file_get_contents($dotenv);
    $lines = explode("\n", $content);

    foreach ($lines as $line) {
        if (empty($line) || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        putenv("$key=$value");
    }
});

task('local:check-sentry-env', function () {
    invoke('dotenv:load');
    $sentryAuthToken = getenv('SENTRY_AUTH_TOKEN');
    if (! $sentryAuthToken) {
        throw new Exception('SENTRY_LARAVEL_DSN not found in .env');
    }
    set('sentry_auth_token', $sentryAuthToken);
});

task('local:set-sentry-env', function () {
    invoke('dotenv:load');
    $host = get('alias');
    $host = str_replace('binsta', 'stag', $host);
    set('sentry', [
        'organization' => 'nickmous',
        'projects' => [
            $host.'-frontend',
            $host.'-backend',
        ],
        'token' => get('sentry_auth_token'),
        'sentry_server' => 'https://sentry.nickmous.com',
        'environment' => getenv('APP_ENV'),
    ]);
});

desc('Set commit hash as version in .env file');
task('deploy:set-version', function () {
    $commitHash = getCurrentReleaseRevision();
    // replace the commit hash in the .env file
    run("sed -i 's/SENTRY_RELEASE=.*/SENTRY_RELEASE=$commitHash/' {{deploy_path}}/shared/.env");
});

task('deploy:generate-sitemap', artisan('app:generate-sitemap'));

task('deploy', [
    'deploy:prepare',
    'deploy:set-version',
    'deploy:vendors',
    'local:copy-env',
//    'deploy:bun:install',
//    'deploy:bun:build',
    'artisan:storage:link',
    'artisan:config:cache',
    'artisan:route:cache',
    'artisan:view:cache',
    'artisan:event:cache',
    'artisan:migrate',
    'deploy:publish',
]);

// Hooks
before('deploy', 'deploy:check_remote');
before('deploy', 'git:check-local-changes');
before('deploy', 'local:check-sentry-env');
after('deploy:failed', 'deploy:unlock');
after('deploy:failed', 'local:restore-env');
after('deploy', 'local:set-sentry-env');
after('deploy', 'deploy:sentry');
after('deploy', 'deploy:generate-sitemap');
after('deploy', 'local:restore-env');
after('deploy', 'deploy:cleanup');
