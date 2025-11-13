<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::create('sudo_cards', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->unsignedBigInteger('sudo_customer_id')->nullable();
    $table->string('sudo_card_id')->unique(); // Sudo's card ID
    $table->string('provider')->nullable();
    $table->string('brand')->nullable();
    $table->string('last4')->nullable();
    $table->string('status')->default('inactive');
    $table->decimal('balance', 20, 2)->default(0);
    $table->timestamps();
});
