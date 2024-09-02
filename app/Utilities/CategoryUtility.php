<?php

namespace App\Utilities;

use App\Models\Category;
use App\Models\LogisticCategory;
use Illuminate\Support\Arr;

class CategoryUtility
{
    public static function getCategoriesIdInLogistic()
    {
        $categoriesId = LogisticCategory::whereNull('deleted_at')->where('status',1)->pluck('category_id')->toArray();
        $ids = [];

        if (count($categoriesId) > 0) {
            foreach ($categoriesId as $key=>$categoryId) {
                array_push($ids,explode(',',$categoryId));
            }

            $ids = Arr::flatten($ids);
        }
        return $ids;
    }
}