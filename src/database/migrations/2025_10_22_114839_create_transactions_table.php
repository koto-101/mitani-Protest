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
            $table->unsignedBigInteger('chat_room_id')->after('id');
            $table->unsignedBigInteger('purchase_id')->unique();
            $table->string('status', 50);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('buyer_evaluated')->default(false);
            $table->boolean('seller_evaluated')->default(false);
            $table->integer('buyer_unread_count')->default(0);
            $table->integer('seller_unread_count')->default(0);
            $table->timestamps();

            $table->foreign('chat_room_id')->references('id')->on('chat_rooms')->onDelete('cascade');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
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
