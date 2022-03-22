<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('device_token')->nullable();
            $table->text('endpoint')->nullable();
            $table->text('subscription')->nullable();
            $table->string('ip')->nullable();
            $table->string('browser')->nullable();
            $table->smallInteger('type')->default(3)->comment('1: android, 2: ios, 3: web');
            $table->integer('expiry')->nullable();
            $table->integer('user_id')->nullable();
            $table->text('notes')->nullable();
            $table->text('track_log')->nullable();
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
        Schema::dropIfExists('device_tokens');
    }
}
