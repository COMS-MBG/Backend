<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('s_p_p_g_s', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('capacity')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->unsignedBigInteger('pemilik_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // FK users → sppgs
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('sppg_id')
                ->references('id')
                ->on('s_p_p_g_s')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sppg_id']);
        });
        Schema::dropIfExists('s_p_p_g_s');
    }
};