<?php

namespace App\Presenters;

class VehiclePresenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [
            [
                "field" => "id",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('general.id'),
                "visible" => false
            ], [
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/vehicles/general.name'),
                "visible" => true,
            ],[
                "field" => "vehicle_no",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/vehicles/general.vehicle_no'),
                "visible" => true
            ],[
                "field" => "vehicle_type",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/vehicles/general.vehicle_type'),
                "visible" => true
            ],
            [
                "field" => "status",
                "searchable" => false,
                "sortable" => true,
                "title" => trans('general.status'),
                "visible" => true
            ],[
                "field" => "actions",
                "searchable" => false,
                "sortable" => false,
                "switchable" => false,
                "title" => trans('table.actions'),
                "visible" => true,
                "formatter" => "vehiclesActionFormatter",
            ],
            [
                "field" => "created_at",
                "searchable" => true,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.created_at'),
                "formatter" => "dateDisplayFormatter"
            ],
            [
                "field" => "updated_at",
                "searchable" => true,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.updated_at'),
                "formatter" => "dateDisplayFormatter"
            ],
        ];

        return json_encode($layout);
    }


    /**
     * Link to this categories name
     * @return string
     */
    public function nameUrl()
    {
        return (string) link_to_route('vehicles.show', $this->name, $this->id);
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('vehicles.show', $this->id);
    }
}