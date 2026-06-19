<?php

function projectPath(string $path): string
{
    return base_path($path);
}

it('defines a docker compose runtime for php and postgres', function () {
    expect(projectPath('compose.yaml'))->toBeFile();

    $compose = file_get_contents(projectPath('compose.yaml'));

    expect($compose)
        ->toContain('services:')
        ->toContain('app:')
        ->toContain('db:')
        ->toContain('docker/php/Dockerfile')
        ->toContain('postgres:16-alpine');
});

it('provides executable wrapper scripts for local container commands', function () {
    foreach (['bin/test', 'bin/artisan', 'bin/composer', 'bin/dev'] as $script) {
        expect(projectPath($script))->toBeFile();
        expect(is_executable(projectPath($script)))->toBeTrue();
    }

    expect(file_get_contents(projectPath('bin/test')))
        ->toContain('docker compose run --rm')
        ->toContain('vendor/bin/pest');

    expect(file_get_contents(projectPath('bin/artisan')))->toContain('docker compose run --rm');
    expect(file_get_contents(projectPath('bin/composer')))->toContain('docker compose run --rm');
    expect(file_get_contents(projectPath('bin/dev')))->toContain('docker compose up');
});

it('declares bin test as the canonical local test command', function () {
    $openSpecConfig = file_get_contents(projectPath('openspec/config.yaml'));

    expect($openSpecConfig)
        ->toContain('command: "bin/test"')
        ->toContain('test_command: "bin/test"');
});

it('pins phpunit database settings to the isolated postgres testing database', function () {
    $phpunitConfig = file_get_contents(projectPath('phpunit.xml'));

    expect($phpunitConfig)
        ->toContain('<env name="DB_CONNECTION" value="pgsql"/>')
        ->toContain('<env name="DB_HOST" value="db"/>')
        ->toContain('<env name="DB_PORT" value="5432"/>')
        ->toContain('<env name="DB_DATABASE" value="raffles_testing"/>')
        ->toContain('<env name="DB_USERNAME" value="postgres"/>');
});

it('forces bin test to boot with explicit testing database overrides', function () {
    $script = file_get_contents(projectPath('bin/test'));

    expect($script)
        ->toContain('APP_ENV=testing')
        ->toContain('TEST_DB_CONNECTION="pgsql"')
        ->toContain('DB_CONNECTION="$TEST_DB_CONNECTION"')
        ->toContain('DB_DATABASE="$TEST_DB_DATABASE"')
        ->toContain('CREATE DATABASE')
        ->toContain('./vendor/bin/pest "$@"');
});

it('guards bin test against reusing the normal development database', function () {
    $script = file_get_contents(projectPath('bin/test'));

    expect($script)
        ->toContain('DB_TEST_DATABASE')
        ->toContain('DEV_DB_DATABASE')
        ->toContain('Refusing to run tests against the development database')
        ->toContain('TEST_DB_DATABASE == DEV_DB_DATABASE')
        ->toContain('postgres|template0|template1')
        ->toContain('exit 1');
});
