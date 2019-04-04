<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id')->index()->unique();
            $table->unsignedInteger('notify_count')->default(0);
            $table->unsignedInteger('max_notify_count')->default(0);
            $table->unsignedInteger('next_notify_at')->nullable();
            $table->longText('notification_payload')->nullable();
            $table->text('send_targets');
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
        Schema::dropIfExists('alerts');
    }
}
