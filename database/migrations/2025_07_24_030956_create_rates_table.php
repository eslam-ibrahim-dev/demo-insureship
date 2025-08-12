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
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('osis_client')->onDelete('cascade');
            $table->string('info');
            $table->string('carrier')->nullable();
            $table->decimal('rate_domestic', 8, 2)->nullable();
            $table->enum('rate_type_domestic', ['value', 'percentage'])->default('value');
            $table->decimal('rate_international', 8, 2)->nullable();
            $table->enum('rate_type_international', ['value', 'percentage'])->default('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
