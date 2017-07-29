<?php namespace RainLab\UserPlus\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UserAddMobileField extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('users', 'mobile')) {
            return;
        }

        Schema::table('users', function($table)
        {
            $table->string('mobile', 100)->nullable();
        });
    }

    public function down()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function ($table) {
                $table->dropColumn(['mobile']);
            });
        }
    }
}
