<?php

namespace App\Presenters;

class PodPresenter
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
                "field" => "pod_id",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/pod/general.pod_id'),
                "visible" => true,
            ],[
                "field" => "num_of_item",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/pod/general.no_of_item'),
                "visible" => true,
            ],
            [
                "field" => "vehicle",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/pod/general.vehicle'),
                "visible" => true
            ],
            [
                "field" => "rider",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/pod/general.rider'),
                "visible" => true,
                // "formatter" => "usersLinkFormatter"
            ],
            [
                "field" => "supplier",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/pod/general.supplier'),
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
                "formatter" => "podActionFormatter",
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
        return (string) link_to_route('pod.show', $this->name, $this->id);
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('pod.show', $this->id);
    }
}