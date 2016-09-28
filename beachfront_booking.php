<?php

/*
Plugin Name: Flodesign Beachfront API
Plugin URI:
Description: API for booking management on beachfrontvillas.tc
Version: 1.0
Author: Craig Thompson
Author URI: http://flodesign.co.uk
*/

add_action( 'rest_api_init', function () {
    require_once(plugin_dir_path(__FILE__) . '/Beachfront_Booking_API.php');
    $api = new Beachfront_Booking_API();
    $api->register_routes();
});