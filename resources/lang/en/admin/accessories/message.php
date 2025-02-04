<?php

return array(

    'does_not_exist' => 'The accessory [:id] does not exist.',
    'assoc_users'	 => 'This accessory currently has :count items checked out to users. Please check in the accessories and and try again. ',

    'create' => array(
        'error'   => 'The inventory was not created, please try again.',
        'success' => 'The inventory was successfully created.'
    ),

    'update' => array(
        'error'   => 'The accessory was not updated, please try again',
        'success' => 'The accessory was updated successfully.'
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this accessory?',
        'error'   => 'There was an issue deleting the accessory. Please try again.',
        'success' => 'The accessory was deleted successfully.'
    ),

     'checkout' => array(
        'error'   		=> 'Inventory was not checked out, please try again',
        'success' 		=> 'Inventory checked out successfully.',
        'user_does_not_exist' => 'That user is invalid. Please try again.'
    ),

    'checkin' => array(
        'error'   		=> 'Inventory was not checked in, please try again',
        'success' 		=> 'Inventory checked in successfully.',
        'user_does_not_exist' => 'That user is invalid. Please try again.'
    )


);
