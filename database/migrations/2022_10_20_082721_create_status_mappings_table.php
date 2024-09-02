<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('status_id')->unsigned();
            $table->string('mapped_code')->nullable(false);
            $table->timestamps();
        
            $table->foreign('status_id')->references('id')->on('status_labels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status_mappings');
    }
}
