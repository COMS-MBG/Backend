<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('school_name');
            $table->string('npsn')->nullable()->unique();
            $table->string('school_type', 50);           // SMA, SMK, MA, etc.
            $table->string('ownership_status', 50);      // public, private
            $table->text('address')->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('portion_count')->default(0);
            $table->uuid('sppg_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            // ── Indexes for frequent queries ──
            $table->index('school_type');
            $table->index('ownership_status');
            $table->index('district');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
