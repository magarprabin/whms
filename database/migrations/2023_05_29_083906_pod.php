<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Pod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('pod', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pod_id');
            $table->string('num_of_item');
            $table->string('image');
            $table->integer('supplier_id');         
            $table->integer('rider_id');
            $table->integer('vehicle_id');
            $table->string('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('status');
            $table->softDeletes();
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
        //
        Schema::dropIfExists('pod');

    }
}
