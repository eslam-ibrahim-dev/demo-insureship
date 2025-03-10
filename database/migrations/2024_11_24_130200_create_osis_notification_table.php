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
        Schema::create('osis_notification', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id');
            $table->string('type');
            $table->string('message');
            $table->string('url');
            $table->enum('unread' , [1 , 0]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('osis_notification');
    }
};
