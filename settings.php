<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// PLUGIN SETTINGS

$plugin_settings = array(

    'enable_forms'              => ( get_option( 'mautic_woocommerce_settings_mautic_enable_forms'           ) == 'yes' ) ? TRUE : FALSE,
    'enable_api'                => ( get_option( 'mautic_woocommerce_settings_mautic_enable_api'             ) == 'yes' ) ? TRUE : FALSE,
    'add_fields'                => ( get_option( 'mautic_woocommerce_settings_mautic_add_fields'             ) == 'yes' ) ? TRUE : FALSE,
    'check_fields'              => ( get_option( 'mautic_woocommerce_settings_mautic_check_fields'           ) == 'yes' ) ? TRUE : FALSE,
    'add_tags'                  => ( get_option( 'mautic_woocommerce_settings_mautic_add_tags'               ) == 'yes' ) ? TRUE : FALSE,
	'add_id_to_tags'            => ( get_option( 'mautic_woocommerce_settings_mautic_add_id_to_tags'         ) == 'yes' ) ? TRUE : FALSE,
    'add_note'                  => ( get_option( 'mautic_woocommerce_settings_mautic_add_note'               ) == 'yes' ) ? TRUE : FALSE,
    'add_phone'                 => ( get_option( 'mautic_woocommerce_settings_mautic_add_phone'              ) == 'yes' ) ? TRUE : FALSE,
    'add_billing'               => ( get_option( 'mautic_woocommerce_settings_mautic_add_billing'            ) == 'yes' ) ? TRUE : FALSE,
    'use_maxmind_location'      => TRUE, // ( get_option( 'mautic_woocommerce_settings_mautic_use_maxmind_location'   ) == 'yes' ) ? TRUE : FALSE,
    'add_general_tags'          =>   get_option( 'mautic_woocommerce_settings_mautic_add_general_tags'       ),
    'send_not_completed_orders' => ( get_option( 'mautic_woocommerce_settings_mautic_send_partial_statuses'  ) == 'yes' ) ? TRUE : FALSE

);

// MAUTIC BASIC API SETTINGS

$basic_api_settings = array(

    'apiURL'     => get_option( 'mautic_woocommerce_settings_server'            ), 
    'userName'   => get_option( 'mautic_woocommerce_settings_mautic_username'   ),  
    'password'   => get_option( 'mautic_woocommerce_settings_mautic_password'   ), 
    'sku_filter' => get_option( 'mautic_woocommerce_settings_mautic_sku_filter' )             

);

$enable_multi_instance = FALSE;

// var_dump($plugin_settings);
// die();


