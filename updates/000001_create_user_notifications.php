<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rainlab_user_notifications', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('baseid')->nullable()->index();
            $table->string('event_type');
            $table->bigInteger('user_id')->unsigned()->nullable()->index();
            $table->string('icon')->nullable();
            $table->string('type')->nullable();
            $table->text('body')->nullable();
            $table->mediumText('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rainlab_user_notifications');
    }
};
