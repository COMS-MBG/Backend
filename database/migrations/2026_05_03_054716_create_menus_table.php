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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();

            // =========================
            // BASIC INFO
            // =========================
            $table->string('name'); // ❗ penting (yang sebelumnya error)
            $table->date('week_start');
            $table->date('week_end');

            // status: planned | scheduled | published | archived
            $table->string('status')->default('planned');

            $table->text('notes')->nullable();

            // timestamps
            $table->timestamps();

            // soft delete (karena model kamu pakai SoftDeletes)
            $table->softDeletes();

            // optional index biar query cepat
            $table->index(['week_start', 'week_end']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};