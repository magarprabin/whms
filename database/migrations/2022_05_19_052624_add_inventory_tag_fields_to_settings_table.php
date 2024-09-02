<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInventoryTagFieldsToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('settings', function (Blueprint $table) {
            $table->integer('auto_increment_inventory')->default(1);
            $table->string('auto_increment_inventory_prefix')->default('BizInv');
            $table->string('inventory_zerofill_count')->default(5);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('auto_increment_inventory');
            $table->dropColumn('auto_increment_inventory_prefix');
            $table->dropColumn('inventory_zerofill_count');
        });
    }
}
