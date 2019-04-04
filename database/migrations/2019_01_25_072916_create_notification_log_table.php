<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('alert_id')->index()->nullable();
            $table->unsignedInteger('device_id')->index()->nullable();
            $table->unsignedInteger('contact_id')->index()->nullable();
            $table->unsignedInteger('notify_count')->nullable();
            $table->string('email');
            $table->string('name')->nullable();
            $table->text('content')->nullable();
            $table->unsignedTinyInteger('job_status')->default(0);
            $table->timestamp('created_at')->nullable()->default(DB::raw('now()'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_log');
    }
}
