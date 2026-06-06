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
    Schema::table('sppg_draft_partners', function (Blueprint $table) {
        $table->string('level', 20)->nullable()->change();
        $table->string('school_status', 20)->nullable()->change();
        $table->text('address')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('sppg_draft_partners', function (Blueprint $table) {
        $table->string('level', 20)->nullable(false)->change();
        $table->string('school_status', 20)->nullable(false)->change();
        $table->text('address')->nullable(false)->change();
    });
}
};
