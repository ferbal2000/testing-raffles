<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('raffles')
            ->whereNull('title')
            ->chunkById(100, function ($raffles): void {
                foreach ($raffles as $raffle) {
                    DB::table('raffles')
                        ->where('id', $raffle->id)
                        ->update(['title' => "Sorteo #{$raffle->id}"]);
                }
            });
    }

    public function down(): void
    {
        // Backfilled titles are retained so rollback never destroys raffle data.
    }
};
