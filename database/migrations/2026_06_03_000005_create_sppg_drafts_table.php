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
        Schema::create('sppg_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('submission_number', 50)->nullable()->unique();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('source', 20)->default('internal'); // internal, public
            $table->json('form1_data')->nullable(); // Data SPPG
            $table->json('form2_data')->nullable(); // Data Admin SPPG
            $table->json('form3_data')->nullable(); // Data Ahli Gizi & Admin Logistik
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('confirmed_latitude', 10, 8)->nullable();
            $table->decimal('confirmed_longitude', 11, 8)->nullable();
            $table->string('point_status', 20)->nullable(); // green, yellow, red
            $table->boolean('map_confirmed')->default(false);
            $table->string('status', 20)->default('draft'); // draft, submitted, registered
            $table->dateTime('submitted_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sppg_drafts');
    }
};
