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
        Schema::create('osis_offer', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('link_name', 255)->nullable(); // Optional link name
            $table->text('terms')->nullable(); // Warranty terms
            $table->string('icon', 255)->nullable(); // URL or file path for the icon
            $table->date('coverage_start')->nullable(); // Coverage start date
            $table->integer('coverage_duration')->nullable(); // Coverage duration in days
            $table->date('file_claim_start')->nullable(); // Claim filing start date
            $table->integer('file_claim_duration')->nullable(); // Claim filing duration in days
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
