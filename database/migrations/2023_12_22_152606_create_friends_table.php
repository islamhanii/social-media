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
        Schema::create('friends', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("sender_id");
            $table->foreign("sender_id")->references("id")->on("users")->onDelete("CASCADE");
            $table->unsignedBigInteger("receiver_id");
            $table->foreign("receiver_id")->references("id")->on("users")->onDelete("CASCADE");
            $table->boolean("is_accepted")->default(0);
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
        Schema::dropIfExists('friends');
    }
};
