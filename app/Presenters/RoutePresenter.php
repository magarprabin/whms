<?php

namespace App\Presenters;

/**
 * Class RoutePresenter
 * @package App\Presenters
 */
class RoutePresenter extends Presenter
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
                "title" => trans('general.name'),
                "visible" => true,
            ],[
                "field" => "start_location",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/routes/general.start_location'),
                "visible" => true,
            ],[
                "field" => "end_location",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/routes/general.end_location'),
                "visible" => true,
            ],[
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
                "formatter" => "routesActionFormatter",
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
