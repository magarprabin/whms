<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

class Route extends SnipeModel
{
    protected $presenter = 'App\Presenters\RoutePresenter';
    use Presentable;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'routes';
    protected $hidden = ['deleted_at'];

    /**
     * Category validation rules
     */
    public $rules = array(
        'name'   => 'required|min:1|max:255|unique_undeleted',
        'start_location'   => 'required|min:1|max:255',
        'end_location'   => 'required|min:1|max:255',
        'start_lat'   => 'nullable',
        'start_lng'   => 'nullable',
        'end_lat'   => 'nullable',
        'end_lng'   => 'nullable',
        'status'   => 'boolean',
        'supplier_id'   => 'required',
    );

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var boolean
     */
    protected $injectUniqueIdentifier = true;
    use ValidatingTrait;
    use UniqueUndeletedTrait;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'start_location',
        'end_location',
        'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
        'status',
        'supplier_id'
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'start_location'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [];

//    /**
//     * Checks if Route can be deleted
//     *
//     * @author [Dan Meltzer] [<dmeltzer.devel@gmail.com>]
//     * @since [v5.0]
//     * @return bool
//     */
//    public function isDeletable()
//    {
//        return (Gate::allows('delete', $this)
//            && ($this->itemCount() == 0));
//    }
}
