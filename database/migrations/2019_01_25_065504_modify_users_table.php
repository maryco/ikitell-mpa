<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersTable extends Migration
{
    private $tableName = 'users';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->unsignedTinyInteger('plan')->after('remember_token');
            $table->boolean('ban')->default(0)->after('plan');
            $table->text('image')->nullable()->after('ban');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('plan');
            $table->dropColumn('ban');
            $table->dropColumn('image');
            $table->dropSoftDeletes();
        });
    }
}
