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
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sppg_id')->constrained('s_p_p_g_s')->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->string('batch_number', 100)->nullable();
            $table->decimal('quantity', 10, 3);
            $table->string('unit', 20); // enum(kg,liter,gram,ml,pcs)
            $table->decimal('price_per_unit', 12, 2);
            $table->date('purchase_date');
            $table->date('expiry_date');
            $table->string('supplier', 255);
            $table->string('storage_type', 50); // enum(dry,chilled,frozen)
            $table->string('storage_location', 255)->nullable();
            $table->string('sku', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 50)->default('pending'); // enum(pending,available,low,empty,expired)
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->string('proof_document', 500)->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('sppg_id');
            $table->index('ingredient_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
