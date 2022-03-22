<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificatonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notificatons', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('push_message_id');
            $table->bigInteger('device_token_id');
            $table->smallInteger('status')->nullable()->comment('1: queued, 2: pushed, 3: mailed, 4: sent (in-app-message)');
            $table->text('track_log')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('notificatons');
    }
}
