<?php namespace RainLab\UserPlus\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UserAddProfileFields extends Migration
{
    public function up()
    {
        if (Schema::hasColumns('users', ['phone', 'company', 'street_addr', 'city', 'zip'])) {
            return;
        }

        Schema::table('users', function($table)
        {
            $table->string('phone', 100)->nullable();
            $table->string('company', 100)->nullable();
            $table->string('street_addr')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('zip', 20)->nullable();
        });
    }

    public function down()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function ($table) {
                $table->dropColumn(['phone', 'company', 'street_addr', 'city', 'zip']);
            });
        }
    }
}
