<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bulksms', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index();
            $table->integer('transid')->default(0);
            $table->text('number')->nullable();
            $table->text('message')->nullable();
            $table->string('sender')->nullable();
            $table->integer('total_number')->nullable();
            $table->integer('total_wrong_number')->nullable();
            $table->integer('total_real_number')->nullable();
            $table->text('wrong_number')->nullable();
            $table->text('real_number')->nullable();
            $table->double('amount',20,2)->nullable();
            $table->double('old_balance',20,2)->nullable();
            $table->double('new_balance',20,2)->nullable();
            $table->string('api_reference')->nullable();
            $table->string('api_name')->nullable();
            $table->string('system')->default('WEB');
            $table->text('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bulksms');
    }
};
