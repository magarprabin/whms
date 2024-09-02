<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Pod_asset extends Model
{
    //
    protected $table = "pod_asset";

    protected $fillable = [
        'po_id',
        'pod_id',
        'asset_id',
        'count',
        'status'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class,'asset_id');
    }
}
