<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('permissions', 'module')) {
                $table->string('module')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('permissions', 'feature')) {
                $table->string('feature')->nullable()->after('module');
            }
            if (!Schema::hasColumn('permissions', 'action')) {
                $table->enum('action', ['create', 'read', 'update', 'delete'])->nullable()->after('feature');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['slug', 'module', 'feature', 'action']);
        });
    }
};