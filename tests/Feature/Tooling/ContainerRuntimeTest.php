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
