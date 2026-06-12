<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sppg_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->tinyInteger('rating')->unsigned(); // 1-5
            $table->text('comment');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->foreign('sppg_id')
                  ->references('id')
                  ->on('s_p_p_g_s')
                  ->onDelete('set null');
        });

        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('otp_code', 6);
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
        Schema::dropIfExists('reviews');
    }
};
