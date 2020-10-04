<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePerfectMoneyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perfect_money_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('status', array('banking','paid','failed','canceled'))->default('banking');
            $table->string('payment_id', 191);
            $table->string('payee_account', 191);
            $table->float('payment_amount');
            $table->string('payment_units', 191);
            $table->string('payment_batch_num', 191)->nullable();
            $table->string('payer_account', 191)->nullable();
            $table->string('timestampgmt', 191)->nullable();
            $table->string('v2_hash', 191)->nullable();
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
        Schema::dropIfExists('perfect_money_transactions');
    }
}
