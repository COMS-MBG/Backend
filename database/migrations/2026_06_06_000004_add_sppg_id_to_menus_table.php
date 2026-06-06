<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->unsignedBigInteger('sppg_id')->nullable()->after('id');
            $table->foreign('sppg_id')->references('id')->on('s_p_p_g_s')->nullOnDelete();
            $table->index('sppg_id');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['sppg_id']);
            $table->dropColumn('sppg_id');
        });
    }
};
