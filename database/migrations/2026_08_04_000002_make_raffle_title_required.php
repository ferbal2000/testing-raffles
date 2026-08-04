<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                        ->whereNull('title')
                        ->update(['title' => "Sorteo #{$raffle->id}"]);
                }
            });

        Schema::table('raffles', function (Blueprint $table) {
            $table->string('title', 100)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('raffles', function (Blueprint $table) {
            $table->string('title', 100)->nullable()->change();
        });
    }
};
