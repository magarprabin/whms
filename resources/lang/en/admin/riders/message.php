<?php

return array(

    'does_not_exist' => 'Rider does not exist.',
    'assoc_models'	 => 'This rider is currently associated with at least one model and cannot be deleted. Please update your models to no longer reference this rider and try again. ',
    'assoc_items'	 => 'This rider is currently associated with at least one :asset_type and cannot be deleted. Please update your :asset_type  to no longer reference this rider and try again. ',

    'create' => array(
        'error'   => 'Rider was not created, please try again.',
        'success' => 'Rider created successfully.'
    ),

    'update' => array(
        'error'   => 'Rider was not updated, please try again',
        'success' => 'Rider updated successfully.'
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this rider?',
        'error'   => 'There was an issue deleting the rider. Please try again.',
        'success' => 'The rider was deleted successfully.'
    )

);
