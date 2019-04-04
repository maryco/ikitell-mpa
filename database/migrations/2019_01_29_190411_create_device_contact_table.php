<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_contact', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('contact_id');
            $table->timestamps();
            $table->unique(['device_id', 'contact_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_contact');
    }
}
