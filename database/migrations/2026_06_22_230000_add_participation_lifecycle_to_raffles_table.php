<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('raffles', function (Blueprint $table) {
            $table->timestamp('participation_opened_at')->nullable()->after('ends_at');
            $table->timestamp('participation_closed_at')->nullable()->after('participation_opened_at');
            $table->string('participation_closed_reason', 32)->nullable()->after('participation_closed_at');
            $table->foreignId('participation_closed_by_admin_id')
                ->nullable()
                ->after('participation_closed_reason')
                ->constrained('admins')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raffles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('participation_closed_by_admin_id');
            $table->dropColumn([
                'participation_opened_at',
                'participation_closed_at',
                'participation_closed_reason',
            ]);
        });
    }
};
