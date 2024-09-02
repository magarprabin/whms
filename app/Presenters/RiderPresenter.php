<?php

namespace App\Presenters;

class RiderPresenter
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
                "field" => "username",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/users/table.username'),
                "visible" => true,
                "formatter" => "usersLinkFormatter"
            ],[
                "field" => "phone",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/users/table.phone'),
                "visible" => true
            ],[
                "field" => "vehicle_type",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/riders/general.vehicle_type'),
                "visible" => true
            ],
            [
                "field" => "shift_from_time",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/riders/general.shift_from_time'),
                "visible" => true
            ],
            [
                "field" => "shift_to_time",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/riders/general.shift_to_time'),
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
                "formatter" => "ridersActionFormatter",
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
        return (string) link_to_route('routes.show', $this->name, $this->id);
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('routes.show', $this->id);
    }
}