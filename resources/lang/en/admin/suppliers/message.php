<?php

return array(

    'does_not_exist' => 'Vendor does not exist.',


    'create' => array(
        'error'   => 'Vendor was not created, please try again.',
        'success' => 'Vendor created successfully.'
    ),

    'update' => array(
        'error'   => 'Vendor was not updated, please try again',
        'success' => 'Vendor updated successfully.'
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this supplier?',
        'error'   => 'There was an issue deleting the supplier. Please try again.',
        'success' => 'Vendor was deleted successfully.',
        'assoc_assets'	 => 'This supplier is currently associated with :asset_count asset(s) and cannot be deleted. Please update your assets to no longer reference this supplier and try again. ',
        'assoc_licenses'	 => 'This supplier is currently associated with :licenses_count licences(s) and cannot be deleted. Please update your licenses to no longer reference this supplier and try again. ',
        'assoc_maintenances'	 => 'This supplier is currently associated with :asset_maintenances_count asset maintenances(s) and cannot be deleted. Please update your asset maintenances to no longer reference this supplier and try again. ',
    )

);
