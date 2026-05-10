<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_schedule_id')->constrained('delivery_schedules')->onDelete('cascade');
            $table->foreignId('courier_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('speed_kmh')->nullable();
            $table->float('heading_degrees')->nullable()->comment('0-360 compass bearing');
            $table->float('accuracy_meters')->nullable();
            $table->timestamp('recorded_at')->useCurrent();

            $table->index(['delivery_schedule_id', 'recorded_at']);
            $table->index(['courier_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_locations');
    }
};