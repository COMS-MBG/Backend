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
        Schema::table('feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('feedback', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('feedback', 'role')) {
                $table->string('role')->nullable()->after('name');
            }
            if (!Schema::hasColumn('feedback', 'message')) {
                $table->text('message')->after('role');
            }
            if (!Schema::hasColumn('feedback', 'rating')) {
                $table->unsignedTinyInteger('rating')->default(5)->after('message');
            }
            if (!Schema::hasColumn('feedback', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('rating');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $colsToDrop = [];
            foreach (['name', 'role', 'message', 'rating', 'is_approved'] as $col) {
                if (Schema::hasColumn('feedback', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
