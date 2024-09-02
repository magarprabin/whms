<?php

return array(

    'does_not_exist' => 'Route does not exist.',
    'assoc_models'	 => 'This route is currently associated with at least one model and cannot be deleted. Please update your models to no longer reference this route and try again. ',
    'assoc_items'	 => 'This route is currently associated with at least one :asset_type and cannot be deleted. Please update your :asset_type  to no longer reference this route and try again. ',

    'create' => array(
        'error'   => 'Route was not created, please try again.',
        'success' => 'Route created successfully.'
    ),

    'update' => array(
        'error'   => 'Route was not updated, please try again',
        'success' => 'Route updated successfully.'
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this route?',
        'error'   => 'There was an issue deleting the route. Please try again.',
        'success' => 'The route was deleted successfully.'
    )

);
