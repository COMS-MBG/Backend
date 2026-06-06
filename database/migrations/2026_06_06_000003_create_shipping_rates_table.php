<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_type')->unique()->comment('motorcycle, car, van, truck');
            $table->decimal('rate_per_km', 10, 2)->comment('Rate in IDR per kilometer');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Insert default rates
        DB::table('shipping_rates')->insert([
            ['vehicle_type' => 'motorcycle', 'rate_per_km' => 2500,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['vehicle_type' => 'car',        'rate_per_km' => 4000,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['vehicle_type' => 'van',        'rate_per_km' => 6000,  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['vehicle_type' => 'truck',      'rate_per_km' => 10000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
