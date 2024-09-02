<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Requests\ApiRequest;
use App\Models\Supplier;

class PullVendors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pull_vendors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ApiRequest = new ApiRequest();
        $result = $ApiRequest->apiCall('GET', 'stores', '', array());
        if (isset($result['stores'])) {
            foreach ($result['stores'] as $store) {
                if ($supplier = Supplier::find($store->company_id)) {
                    $mode = 'update';
                    $message = ' updated.';
                } else {
                    $mode = 'add';
                    $message = ' added.';
                }
                $supplier = ($mode == 'update') ? Supplier::find($store->company_id) : new Supplier();
                $supplier->id = isset($store->company_id) ? $store->company_id : '';
                $supplier->name  = isset($store->company) ? $store->company : '';
                $supplier->email = isset($store->email) ? $store->email : '';
                $supplier->address = isset($store->address) ? $store->address : '';
                $supplier->address2 = isset($store->address2) ? $store->address2 : '';
                $supplier->city = isset($store->city) ? $store->city : '';
                $supplier->state = isset($store->state) ? $store->state : '';
                $supplier->country = isset($store->country) ? $store->country : '';
                $supplier->zip = isset($store->zipcode) ? $store->zipcode : '';
                $supplier->contact = isset($store->contact) ? $store->contact : '';
                $supplier->phone = isset($store->phone) ? $store->phone : '';
                $supplier->fax = isset($store->fax) ? $store->fax : '';
                $supplier->notes = isset($store->notes) ? $store->notes : '';
                $supplier->url = isset($store->url) ? $store->url : '';
                $supplier->user_id = isset($store->user_id) ? $store->user_id : '';
                $supplier->save();
                echo 'Supplier:' . $store->company_id . ' => ' . "'" . $store->company . "'" . $message . PHP_EOL;
            }
        } else {
            echo 'Result Stores Unavailable!!' . PHP_EOL;
        }
    }
}
