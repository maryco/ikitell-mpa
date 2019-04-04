<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id')->index();
            $table->integer('assigned_user_id')->index()->nullable();
            $table->unsignedInteger('passport_client_id')->nullable();
            $table->string('mac_address', 100)->nullable();
            $table->unsignedTinyInteger('type')->default(0);
            $table->unsignedInteger('rule_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('reset_word')->nullable();
            $table->boolean('in_alert')->default(0);
            $table->boolean('in_suspend')->default(0);
            $table->string('user_name')->nullable();
            $table->text('image')->nullable();
            $table->unsignedInteger('reported_at')->nullable();
            $table->unsignedInteger('report_reserved_at')->nullable();
            $table->dateTime('suspend_start_at')->nullable();
            $table->dateTime('suspend_end_at')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('devices');
    }
}
