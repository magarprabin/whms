<?php

return array(

    'does_not_exist' => 'Vehicle does not exist.',
    'assoc_models'	 => 'This vehicle is currently associated with at least one model and cannot be deleted. Please update your models to no longer reference this vehicle and try again. ',
    'assoc_items'	 => 'This vehicle is currently associated with at least one :asset_type and cannot be deleted. Please update your :asset_type  to no longer reference this vehicle and try again. ',

    'create' => array(
        'error'   => 'Vehicle was not created, please try again.',
        'success' => 'Vehicle created successfully.'
    ),

    'update' => array(
        'error'   => 'Vehicle was not updated, please try again',
        'success' => 'Vehicle updated successfully.'
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this vehicle?',
        'error'   => 'There was an issue deleting the vehicle. Please try again.',
        'success' => 'The vehicle was deleted successfully.'
    )

);
