<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->comment('Unique auto generated reference Id');
            $table->string('reference_id')->comment('Unique Order no/Reference id');
            $table->string('transaction_id')->nullable()->comment('Transaction id returned by billdesk');
            $table->string('transaction_status')->default('0002')->comment('Transaction status code, default Pending');
            $table->text('request_payload')->comment('Request data sent to billdesk');
            $table->text('response_payload')->nullable()->comment('Response data received from billdesk');
            $table->string('response_format')->default('HTML')->comment('Response format HTML/JSON');
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
        Schema::dropIfExists('transactions');
    }
}
