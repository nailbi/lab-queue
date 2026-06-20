<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * registration_open — открыта ли запись в очередь по предмету.
     * Староста может «заморозить» запись перед самой защитой,
     * чтобы очередь не менялась: новые студенты встать не смогут,
     * но уже записавшиеся остаются и могут выйти.
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('registration_open')->default(true)->after('lab_count');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('registration_open');
        });
    }
};
