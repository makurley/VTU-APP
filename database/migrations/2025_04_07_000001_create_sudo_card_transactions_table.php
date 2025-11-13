<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSudoCardTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('sudo_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sudo_card_id');
            $table->string('transaction_id')->unique();
            $table->string('type');
            $table->decimal('amount', 20, 2);
            $table->text('description')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sudo_card_transactions');
    }
}
