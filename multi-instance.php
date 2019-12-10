<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*

HOW THIS FEATURE WORKS:
-----------------------

THIS FEATURE MUST BE ENABLED IN THE MAIN CODE OF THE PLUGIN WITH THE $enable_multi_instance VARIABLE, IF NOT THE ONLY INSTANCE THAT WILL RECEIVE DATA WILL BE THE BASIC API CONFIGURED IN THE SETTINGS OF THIS PLUGIN.

ALL THE INSTANCES INCLUDED IN THIS ARRAY WILL BE ADDED TO THE PLUGIN PROCESSES.

IF THIS FEATURE IS ENABLED THE BASIC API SETTINGS CONFIGURED IN THE SETTINGS OF THE PLUGIN WILL BE ADDED AUTOMATICALLY AS ANOTHER INSTANCE.

THE INSTANCES ARE FILTERED BY THE SKU_FILTER, IF THE PURCHASED PRODUCT INCLUDE THE TEXT IN THE SKU_FILTER IN THEIR SKU THE PRODUCT WILL PASS AND THE DATA WILL BE SEND TO THAT INSTANCE.

THE SKU_FILTER MUST MATCH UPPERCASE AND LOWERCASE LETTERS.

IMPORTANT! ALL THE INSTANCES WITH THE SKU_FILTER EMPTY = '' WILL PASS EVERYTIME AND WILL RECEIVE ALL THE TRANSACTIONS IN THIS WOOCOMMERCE.

Examples:

Product SKU = 'BrandName-ProductCode1'

Will pass for instances with this sku filters:

1
BrandName
Brand
Name
ProductCode
Product
Code
B
n
e

Will not pass for instances with this sku filters:

brandname
BRANDNAME
PRODUCT
productcode
b
A

NOTE THAT IF YOU USE A SIMPLE NUMBER OR A GROUP OF LETTERS YOU MUST ASSURE THAT THE SAME CARACTERS WILL NOT BE USED ON AN INCORRECT WAY IN OTHER PRODUCT SKU.

AN EXAMPLE OF THIS COULD BE A BRAND NAME IN YOUR SKU THAT INCLUDE YOUR FILTER LETTERS OR A CODE BAR IN THE PRODUCT SKU THAT INCLUDE YOUR FILTER NUMBER.

EXAMPLE ON HOW TO FILL THIS ARRAY:
----------------------------------

$mautic_instances = array (

	0 => array (

	    'apiURL'     => "https://localhost/mautic/ol/", 
	    'userName'   => 'woocommerce_ol',           // Api user       
	    'password'   => 'password_ol',              // Secure password
	    'sku_filter' => 'ol',

	),

	1 => array (

	    'apiURL'     => "https://localhost/mautic/xls/", 
	    'userName'   => 'woocommerce_xls',           // Api user       
	    'password'   => 'password_xls',              // Secure password
	    'sku_filter' => 'xls',

	),

	2 => array (

	    'apiURL'     => "https://localhost/mautic/1/", 
	    'userName'   => 'woocommerce_1',           // Api user       
	    'password'   => 'password_1',              // Secure password
	    'sku_filter' => '1',

	),

	3 => array (

	    'apiURL'     => "https://localhost/mautic/em/", 
	    'userName'   => 'woocommerce',           // Api user       
	    'password'   => 'password',              // Secure password
	    'sku_filter' => '',

	),

);

*/

$mautic_instances = array (

);