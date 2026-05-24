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
            $table->string('name')->after('id');
            $table->string('role')->nullable()->after('name');
            $table->text('message')->after('role');
            $table->unsignedTinyInteger('rating')->default(5)->after('message');
            $table->boolean('is_approved')->default(false)->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropColumn(['name', 'role', 'message', 'rating', 'is_approved']);
        });
    }
};
