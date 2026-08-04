<?php

use App\Models\Admin;
use App\Models\Raffle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
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
    $requiredTitlePath = database_path('migrations/2026_08_04_000002_make_raffle_title_required.php');

    if (is_file($requiredTitlePath)) {
        $this->requireTitle = require $requiredTitlePath;

        if (Schema::hasColumn('raffles', 'title') && ! raffleTitleColumn()['nullable']) {
            $this->requireTitle->down();
        }
    }

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

it('defensively backfills transitional nulls before enforcing a bounded required title', function () {
    $requiredTitlePath = database_path('migrations/2026_08_04_000002_make_raffle_title_required.php');

    expect($requiredTitlePath)->toBeFile();

    $this->addTitle->up();
    DB::table('raffles')->insert(collect(range(1, 205))
        ->map(fn (int $id): array => [
            'id' => $id,
            'title' => $id === 101 ? 'Authored title' : null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    $this->requireTitle->up();
    $column = raffleTitleColumn();

    expect(DB::table('raffles')->whereNull('title')->count())->toBe(0)
        ->and(DB::table('raffles')->where('id', 1)->value('title'))->toBe('Sorteo #1')
        ->and(DB::table('raffles')->where('id', 101)->value('title'))->toBe('Authored title')
        ->and(DB::table('raffles')->where('id', 205)->value('title'))->toBe('Sorteo #205')
        ->and(collect($queries)->contains(
            fn (string $query): bool => str_contains($query, '"id" >')
                && str_contains($query, 'order by "id" asc')
        ))->toBeTrue()
        ->and($column['type'])->toContain('(100)')
        ->and($column['nullable'])->toBeFalse()
        ->and($column['default'])->toBeNull();

    expect(fn () => DB::transaction(fn () => DB::table('raffles')->insert([
        'id' => 206,
        'title' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ])))->toThrow(QueryException::class);

    DB::table('raffles')->insert([
        'id' => 207,
        'title' => str_repeat('T', 100),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(fn () => DB::transaction(fn () => DB::table('raffles')->insert([
        'id' => 208,
        'title' => str_repeat('T', 101),
        'created_at' => now(),
        'updated_at' => now(),
    ])))->toThrow(QueryException::class);

    $this->requireTitle->down();

    expect(raffleTitleColumn()['nullable'])->toBeTrue();

    DB::table('raffles')->insert([
        'id' => 209,
        'title' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(DB::table('raffles')->where('id', 209)->value('title'))->toBeNull();
});

it('runs the transitional authoring constraint and rollback boundary', function () {
    expect(database_path('migrations/2026_08_04_000002_make_raffle_title_required.php'))->toBeFile();

    $this->addTitle->up();
    DB::table('raffles')->insert([
        'id' => 50,
        'title' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $admin = Admin::factory()->create();
    $host = (string) parse_url((string) config('app.admin_url'), PHP_URL_HOST);
    $url = fn (string $path): string => rtrim((string) config('app.admin_url'), '/').$path;

    $this->actingAs($admin, 'admin')
        ->withServerVariables(['HTTP_HOST' => $host])
        ->post($url('/raffles'), [
            'title' => '  Authored create  ',
            'starts_at' => '',
            'ends_at' => '',
        ])->assertRedirect(route('admin.raffles.index'));

    $created = Raffle::query()->where('title', 'Authored create')->sole();

    $this->withServerVariables(['HTTP_HOST' => $host])
        ->from($url('/raffles/create'))
        ->post($url('/raffles'), [
            'title' => '   ',
            'starts_at' => '',
            'ends_at' => '',
        ])->assertSessionHasErrors('title');

    $this->withServerVariables(['HTTP_HOST' => $host])
        ->from($url("/raffles/{$created->id}/edit"))
        ->patch($url("/raffles/{$created->id}"), [
            'title' => ['invalid'],
            'starts_at' => '2026-07-01T09:15',
            'ends_at' => '',
        ])->assertSessionHasErrors('title');

    expect($created->fresh()->title)->toBe('Authored create')
        ->and($created->fresh()->starts_at)->toBeNull();

    $this->withServerVariables(['HTTP_HOST' => $host])
        ->patch($url("/raffles/{$created->id}"), [
            'title' => '  Authored edit  ',
            'starts_at' => '',
            'ends_at' => '',
        ])->assertRedirect(route('admin.raffles.index'));

    $this->requireTitle->up();

    expect(DB::table('raffles')->where('id', 50)->value('title'))->toBe('Sorteo #50')
        ->and($created->fresh()->title)->toBe('Authored edit')
        ->and(raffleTitleColumn()['nullable'])->toBeFalse();

    expect(fn () => DB::transaction(fn () => DB::table('raffles')->insert([
        'title' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ])))->toThrow(QueryException::class);

    $this->requireTitle->down();
    DB::table('raffles')->insert([
        'title' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(raffleTitleColumn()['nullable'])->toBeTrue()
        ->and(DB::table('raffles')->whereNull('title')->count())->toBe(1);
});
