<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'zip')) {
            Schema::table('users', function($table) {
                $table->string('address_line1')->nullable();
                $table->string('address_line2')->nullable();
                $table->string('company')->nullable();
                $table->string('phone')->nullable();
                $table->string('city')->nullable();
                $table->string('zip')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'country_id')) {
            Schema::table('users', function($table) {
                $table->bigInteger('state_id')->unsigned()->nullable()->index();
                $table->bigInteger('country_id')->unsigned()->nullable()->index();
            });
        }
    }

    public function down()
    {
    }
};
