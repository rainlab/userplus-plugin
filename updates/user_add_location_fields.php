<?php namespace RainLab\UserPlus\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UserAddLocationFields extends Migration
{

    public function up()
    {
        /*
         * These fields were previously owned by RainLab.User
         * so this occurance is detected and migration skips
         * @deprecated Safe to remove if year >= 2017
         */
        if (Schema::hasColumns('users', ['state_id', 'country_id'])) {
            return;
        }

        Schema::table('users', function($table)
        {
            $table->integer('state_id')->unsigned()->nullable()->index();
            $table->integer('country_id')->unsigned()->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('state_id');
            $table->dropColumn('country_id');
        });
    }

}
