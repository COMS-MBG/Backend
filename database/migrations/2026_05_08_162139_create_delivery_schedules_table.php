<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('employees')->onDelete('restrict');
            $table->foreignId('school_id')->constrained('schools')->onDelete('restrict');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('restrict'); // Admin Logistik
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null'); // Admin SPPG
            $table->string('vehicle_type', 50)->nullable()->comment('motorcycle, car, van, truck');
            $table->string('vehicle_plate', 20)->nullable();
            $table->enum('status', ['in_order', 'accepted', 'rejected', 'delivering', 'delivered', 'confirmed', 'revision_required'])
                ->default('in_order');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->text('delivery_notes')->nullable();

            // Rejection
            $table->text('rejection_reason')->nullable();
            $table->string('rejection_photo_path')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Delivery proof
            $table->string('proof_photo_path')->nullable();
            $table->timestamp('proof_submitted_at')->nullable();

            // Admin confirmation
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->text('confirmation_notes')->nullable();

            // Route snapshot (stored after delivery)
            $table->json('route_snapshot')->nullable()->comment('GeoJSON LineString of actual route taken');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'courier_id']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};