<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Accessory;

class AddAutoInventoryTagBaseToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $inventories = Accessory::select('inventory_tag')->whereNull('deleted_at')->get();
        if (!$next = Accessory::nextInventoryAutoIncrement($inventories)) {
            $next = 1;
        }

        Schema::table('settings', function (Blueprint $table) use ($next) {
            $table->bigInteger('next_auto_inventory_tag_base')->default('1');
        });

        //\Log::debug('Setting '.$next.' as default auto-increment');

        if ($settings = App\Models\Setting::first()) {
            $settings->next_auto_inventory_tag_base = $next;
            $settings->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('next_auto_inventory_tag_base');
        });
    }
}
