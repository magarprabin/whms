<?php
namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\LogisticCategory;
use App\Models\Category;
use Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class LogisticCategoriesTransformer
{

    public function transformLogisticCategories (Collection $logisticCategories, $total)
    {
        $array = array();
        foreach ($logisticCategories as $logisticCategory) {
            $array[] = self::transformLogisticCategory($logisticCategory);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformLogisticCategory (LogisticCategory $logisticCategory = null)
    {
        $categories = [];
        foreach($logisticCategory->categories as $cat){
            $categories[] = $cat['name'];
        }
        $categories = implode(',',$categories);
        if ($logisticCategory) {

            $array = [
                'id' => (int) $logisticCategory->id,
                'categories' => e($categories),
                'name' => e($logisticCategory->name),
                'status' => ($logisticCategory->status == 1) ? 'Active' : 'Inactive',
                'created_at' => Helper::getFormattedDateObject($logisticCategory->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($logisticCategory->updated_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', LogisticCategory::class),
                'delete' => $logisticCategory->isDeletable(),
            ];

            $array += $permissions_array;

            return $array;
        }

    }

}
