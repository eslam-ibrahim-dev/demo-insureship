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
        Schema::create('osis_order', function (Blueprint $table) {
            $table->id(); // ID field (auto-incremented)
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('subclient_id');
            $table->unsignedBigInteger('client_offer_id');
            $table->unsignedBigInteger('merchant_id');
            $table->string('merchant_name');
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone');
            $table->string('shipping_address1');
            $table->string('shipping_address2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_state');
            $table->string('shipping_zip');
            $table->string('shipping_country');
            $table->string('billing_address1');
            $table->string('billing_address2')->nullable();
            $table->string('billing_city');
            $table->string('billing_state');
            $table->string('billing_zip');
            $table->string('billing_country');
            $table->string('order_number');
            $table->text('items_ordered');
            $table->decimal('order_total', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->string('currency', 3); // Assuming it's a 3-letter currency code like USD, EUR, etc.
            $table->decimal('coverage_amount', 10, 2)->nullable();
            $table->decimal('shipping_amount', 10, 2);
            $table->string('carrier');
            $table->string('tracking_number')->nullable();
            $table->timestamp('order_date')->useCurrent();
            $table->timestamp('ship_date')->nullable();
            $table->string('source');
            $table->string('order_key')->unique();
            $table->enum('email_status', ['do_not_send', 'pending', 'sent', 'error']);
            $table->timestamp('email_time')->nullable()->useCurrent();
            $table->timestamp('register_date')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->unsignedBigInteger('shipping_log_id')->nullable();
            $table->string('firstname');
            $table->string('lastname');
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->boolean('test_flag')->default(false);
            $table->enum('status', ['active', 'inactive']); // You can add other statuses as needed
            $table->timestamps(); // Created at, Updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
