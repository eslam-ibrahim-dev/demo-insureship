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
        Schema::create('osis_report_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('subclient_id');
            $table->string('subclient_name');
            $table->date('date');
            $table->integer('active');
            $table->integer('inactive');
            $table->float('coverage_amount');
            $table->integer('errors');
            $table->integer('claims_filed');
            $table->integer('claims_paid');
            $table->integer('claims_denied');
            $table->decimal('claims_paid_amount', 10, 2);
            $table->decimal('claims_open_amount', 10, 2);
            $table->decimal('claims_denied_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('osis_report_data');
    }
};
