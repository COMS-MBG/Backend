<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('sppg_id')->nullable(); // null = global role (super_admin)
            $table->softDeletes();
            $table->timestamps();

            // slug is unique per SPPG
            $table->unique(['slug', 'sppg_id']);

            $table->foreign('sppg_id')
                ->references('id')->on('s_p_p_g_s')
                ->nullOnDelete();
        });

        // FK employees → roles (added here because roles table was just created)
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')->on('roles')
                ->nullOnDelete();
        });

        // FK sppgs → users (owner_id, added here because users table already exists)
        Schema::table('s_p_p_g_s', function (Blueprint $table) {
            $table->foreign('pemilik_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('s_p_p_g_s', function (Blueprint $table) {
            $table->dropForeign(['pemilik_id']);
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });
        Schema::dropIfExists('roles');
    }
};