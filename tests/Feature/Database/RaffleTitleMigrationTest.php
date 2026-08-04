<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function raffleTitleMigration(string $filename): Migration
{
    return require database_path("migrations/{$filename}");
}

function raffleTitleColumn(): array
{
    return collect(Schema::getColumns('raffles'))
        ->firstWhere('name', 'title');
}

beforeEach(function () {
    $this->addTitle = raffleTitleMigration('2026_08_04_000000_add_nullable_title_to_raffles_table.php');
    $this->backfillTitles = raffleTitleMigration('2026_08_04_000001_backfill_raffle_titles.php');

    if (Schema::hasColumn('raffles', 'title')) {
        $this->addTitle->down();
    }
});

it('adds a bounded nullable title without a default and reverses safely', function () {
    $this->addTitle->up();

    $column = raffleTitleColumn();

    expect($column['type'])->toContain('(100)')
        ->and($column['nullable'])->toBeTrue()
        ->and($column['default'])->toBeNull();

    DB::table('raffles')->insert([
        ['id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ['id' => 9, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->backfillTitles->up();

    expect(DB::table('raffles')->orderBy('id')->pluck('title', 'id')->all())->toBe([
        3 => 'Sorteo #3',
        9 => 'Sorteo #9',
    ]);

    DB::table('raffles')->insert([
        'id' => 12,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(DB::table('raffles')->where('id', 12)->value('title'))->toBeNull();

    $this->backfillTitles->down();
    expect(DB::table('raffles')->orderBy('id')->pluck('title', 'id')->all())->toBe([
        3 => 'Sorteo #3',
        9 => 'Sorteo #9',
        12 => null,
    ]);

    $this->addTitle->down();

    expect(Schema::hasColumn('raffles', 'title'))->toBeFalse()
        ->and(DB::table('raffles')->orderBy('id')->pluck('id')->all())->toBe([3, 9, 12]);
});

it('backfills only missing titles in stable id based chunks', function () {
    $this->addTitle->up();

    DB::table('raffles')->insert(collect(range(1, 205))
        ->map(fn (int $id): array => [
            'id' => $id,
            'title' => $id === 101 ? 'Existing title' : null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    $this->backfillTitles->up();

    expect(DB::table('raffles')->whereNull('title')->count())->toBe(0)
        ->and(DB::table('raffles')->where('id', 1)->value('title'))->toBe('Sorteo #1')
        ->and(DB::table('raffles')->where('id', 100)->value('title'))->toBe('Sorteo #100')
        ->and(DB::table('raffles')->where('id', 101)->value('title'))->toBe('Existing title')
        ->and(DB::table('raffles')->where('id', 205)->value('title'))->toBe('Sorteo #205')
        ->and(collect($queries)->contains(
            fn (string $query): bool => str_contains($query, '"id" >')
                && str_contains($query, 'order by "id" asc')
        ))->toBeTrue();
});

it('leaves not null enforcement outside the first pull request', function () {
    expect(database_path('migrations/2026_08_04_000002_make_raffle_title_required.php'))
        ->not->toBeFile();
});
