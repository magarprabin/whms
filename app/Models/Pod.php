<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Searchable;

class Pod extends SnipeModel
{
    //
    protected $presenter = 'App\Presenters\PodPresenter';
    use Presentable;
    use SoftDeletes;
    protected $table = "pod";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pod_id',
        'rider_id',
        'supplier_id',
        'vehicle_id',
        'status',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['pod_id', 'supplier_id'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [];

    public function rider()
    {
        return $this->belongsTo(Rider::class,'rider_id');
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class,'vehicle_id');
    }
    public function supplier()
    {
        return $this->belongsTo(supplier::class,'supplier_id');
    }

}
