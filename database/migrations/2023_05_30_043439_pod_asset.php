<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PodAsset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('pod_asset', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('po_id'); 
            $table->string('pod_id');
            $table->integer('asset_id');                  
            $table->integer('count');                  
            $table->boolean('status');
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
        Schema::dropIfExists('pod_asset');
    }
}
