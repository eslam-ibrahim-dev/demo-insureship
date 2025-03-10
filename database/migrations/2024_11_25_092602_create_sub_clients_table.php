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
        Schema::create('osis_subclient', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('name', 255);
            $table->string('referral_id', 255)->nullable();
            $table->string('apikey', 512);
            $table->string('username', 255);
            $table->string('password', 255);
            $table->string('salt', 255);
            $table->integer('email_timeout')->nullable();
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->unsignedBigInteger('affiliate_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('ll_customer_id')->nullable();
            $table->unsignedBigInteger('ll_api_policy_id')->nullable();
            $table->string('ll_key', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->boolean('is_test_account')->default(false);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('osis_subclient');
    }
};
