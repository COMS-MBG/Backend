<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_schedule_id')->constrained('delivery_schedules')->onDelete('restrict');
            $table->foreignId('courier_id')->constrained('employees')->onDelete('restrict');
            $table->foreignId('school_id')->constrained('schools')->onDelete('restrict');
            $table->string('courier_name', 100);
            $table->string('school_name', 150);
            $table->string('school_address', 255)->nullable();
            $table->string('vehicle_type', 50)->nullable();
            $table->string('vehicle_plate', 20)->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->integer('duration_minutes')->storedAs('EXTRACT(EPOCH FROM (arrived_at - departed_at)) / 60');
            $table->string('proof_photo_path')->nullable();
            $table->json('route_snapshot')->nullable()->comment('GeoJSON of the route taken');
            $table->decimal('distance_km', 8, 3)->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('courier_id');
            $table->index('school_id');
            $table->index('departed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_histories');
    }
};