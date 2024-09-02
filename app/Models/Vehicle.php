<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Watson\Validating\ValidatingTrait;

class Vehicle extends SnipeModel
{
    protected $presenter = 'App\Presenters\VehiclePresenter';
    use Presentable;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'vehicles';
    protected $hidden = ['deleted_at'];

    /**
     * Rider validation rules
     */
    public $rules = array(
        'name'   => 'required|string',
        'vehicle_no'   => 'required|string',
        'vehicle_type'   => 'required|string',
        'status'   => 'boolean',
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
        'vehicle_no',
        'vehicle_type',
        'status',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['user_id', 'status'];

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
