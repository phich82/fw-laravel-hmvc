<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePushMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->text('image')->nullable();
            $table->text('conditions')->nullable();
            $table->string('deep_link')->nullable();
            $table->string('message_type')->comment('1: promotion, 2: review');
            $table->string('group')->comment('1: push notification, 2: in-app-message, 3: email');
            $table->smallInteger('pushed')->comment('push notification: 1 - all pushed, 0 - a partial');
            $table->smallInteger('mailed')->comment('sendmail: 1 - all sent, 0 - a partial');
            $table->smallInteger('sent')->comment('in-app-message: 1 - all sent, 0: a partial');
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
        Schema::dropIfExists('push_messages');
    }
}
