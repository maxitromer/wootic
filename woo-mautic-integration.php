<?php

/*
Plugin Name: Wootic
Plugin URI: https://github.com/maxitromer/wootic
Description: Send your Woocommerce order transactions and client data to Mautic
Version: 0.2.0
Author: Maxi Tromer
Author URI: https://github.com/maxitromer
Developer: Maxi Tromer
Developer URI: https://github.com/maxitromer
GitHub Plugin URI: https://github.com/maxitromer/wootic
WC requires at least: 3.0
WC tested up to: 3.7.1
Text Domain: woo_mautic_integration
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require __DIR__ . '/vendor/autoload.php';

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	if ( is_admin() ){
		if (! ( get_option( 'mautic_woocommerce_settings_server' ) ) ) {
			add_action( 'admin_notices', 'mautic_woocommerce_admin_notice_no_configuration' );
		}
		add_filter( 'woocommerce_settings_tabs_array', 'mautic_woocommerce_add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_mautic', 'mautic_woocommerce_settings_tab' );
		add_action( 'woocommerce_update_options_mautic', 'mautic_woocommerce_settings_tab_update' );
	}
	else {
	}
}

/* 
Plug-in management
*/

function mautic_woocommerce_add_settings_tab($settings_tabs) {
//	authorize_mautic();
	$settings_tabs['mautic'] = __( 'Mautic Integration', 'woo_mautic_integration' );
	return $settings_tabs;
}

function mautic_woocommerce_settings_tab() {
    woocommerce_admin_fields( mautic_woocommerce_tab_settings() );
}

function mautic_woocommerce_settings_tab_update() {
    woocommerce_update_options( mautic_woocommerce_tab_settings() );
}

function mautic_woocommerce_tab_settings() {
	$settings = array(

		'mautic_section_title' => array(
			'name' => __('Set the Connection and Options to Integrate Mautic', 'woo_mautic_integration'),
			'type' => 'title',
			'desc' => __('Create a special Mautic user with a strong password for this and preferibly use a TLS (https) connection to be safe.', 'woo_mautic_integration'),
			'id'   => 'mautic_woocommerce_settings_mautic_section_title'
		),
		'server' => array(
			'name'        => __('Mautic URL', 'woo_mautic_integration'),
			'type'        => 'text',
			'css'         => 'min-width:200px;',
			'desc_tip'    => __('Include http or https and final /', 'woo_mautic_integration'),
			'placeholder' =>  __('Your Mautic URL (including http/https)', 'woo_mautic_integration'),
			'id'          => 'mautic_woocommerce_settings_server'
		),

		'mautic_username' => array(
			'name' => __('Mautic User', 'woo_mautic_integration'),
			'type' => 'text',
			'css'  => 'min-width:200px;',
			'id'   => 'mautic_woocommerce_settings_mautic_username'
		),
		'mautic_password' => array(
			'name' => __('Mautic Password', 'woo_mautic_integration'),
			'type' => 'password',
			'css'  => 'min-width:200px;',
			'id'   => 'mautic_woocommerce_settings_mautic_password'
		),
		'mautic_sku_filter' => array(
			'name'     => __('SKU Filter', 'woo_mautic_integration'),
			'desc_tip' => __( 'If the SKU of the product include the exact text in this field the data will be send to Mautic, otherwise will be ignored. Leave blanck to send all the transactions to Mautic.', 'woo_mautic_integration' ),
			'type'     => 'text',
			'css'      => 'min-width:200px;',
			'id'       => 'mautic_woocommerce_settings_mautic_sku_filter'
		),

		'mautic_enable_forms' => array(
			'name'     => __( 'Integrate Using Forms', 'woo_mautic_integration' ),
			'desc'     => __( 'Fastest integration method. Recommended for Mautic instances with big lists of contacts or several campaigns and processes in place.', 'woo_mautic_integration' ),
			'desc_tip' => __( "Requieres manual form setup in Mautic and SKU specific completion per product. (User and password not requiered)", 'woo_mautic_integration' ),
			'std'      => 'yes', // WooCommerce < 2.0
    		'default'  => 'yes', // WooCommerce >= 2.0
			'id'       => 'mautic_woocommerce_settings_mautic_enable_forms',
			'type'     => 'checkbox'
		),

		'mautic_enable_api' => array(
			'name'     => __( 'Integrate Using the API', 'woo_mautic_integration' ),
			'desc'     => __( 'Simpler and more completed integration. Use if you will have a few products and a few contacts to ensure smooth operation.', 'woo_mautic_integration' ),
			'desc_tip' => __( "All the options work with this integration method, even the custom fields could be automatically created.", 'woo_mautic_integration' ),
			'std'      => 'no', // WooCommerce < 2.0
    		'default'  => 'no', // WooCommerce >= 2.0
			'id'       => 'mautic_woocommerce_settings_mautic_enable_api',
			'type'     => 'checkbox'
		),

		'mautic_send_partial_statuses' => array(
			'name'        => __( 'Send Partial Statuses', 'woo_mautic_integration' ),
			'desc'        => __( 'Enable to send all the partial order statuses.', 'woo_mautic_integration' ),
			'desc_tip'    => __( "By default Wootic only send 'completed' and 'refunded' transactions (and 'active' or 'cancelled' for subscriptions).", 'woo_mautic_integration' ),
		    'std'             => 'no', // WooCommerce < 2.0
		    'default'         => 'no', // WooCommerce >= 2.0			
			'id'          => 'mautic_woocommerce_settings_mautic_send_partial_statuses',
			'type'        => 'checkbox'
		),	

		'mautic_add_phone' => array(
			'name'    => __( 'Include Phone', 'woo_mautic_integration' ),
			'desc'    => __( "Include just the contact's phone from the billing section of the checkout.", 'woo_mautic_integration'  ),
		    'std'             => 'yes', // WooCommerce < 2.0
		    'default'         => 'yes', // WooCommerce >= 2.0			
			'id'      => 'mautic_woocommerce_settings_mautic_add_phone',
			'type'    => 'checkbox'
		),

		'mautic_add_billing' => array(
			'name'            => __( 'Include Billing Information', 'woo_mautic_integration' ),
			'desc'            => __( "Include all the contact's billing information when is populated in the checkout.", 'woo_mautic_integration' ),
			'desc_tip'        => __( "This will EXCLUDE TO SYNC the 'city', 'state' and 'country' fields due an incompatibility between Mautic and Woocommerce", 'woo_mautic_integration' ),
		    'std'             => 'yes', // WooCommerce < 2.0
		    'default'         => 'yes', // WooCommerce >= 2.0				
			'id'              => 'mautic_woocommerce_settings_mautic_add_billing',			
			'type'            => 'checkbox'
		),

		'mautic_add_fields' => array(
			'name'     => __( 'Add Custom Fields', 'woo_mautic_integration' ),
			'desc'     => __( 'Add a custom field for every product (and put the status of the order as value).', 'woo_mautic_integration' ),
			'desc_tip' => __( "SKU's product will be used as field alias. The custom field value will be replaced if a new order is created.", 'woo_mautic_integration' ),
			'std'      => 'yes', // WooCommerce < 2.0
    		'default'  => 'yes', // WooCommerce >= 2.0
			'id'       => 'mautic_woocommerce_settings_mautic_add_fields',
			'type'     => 'checkbox'
		),

		'mautic_check_fields' => array(
			'name'     => __( 'Check and Create Fields', 'woo_mautic_integration' ),
			'desc'     => __( 'API ONLY. Check if custom fields exist, if not will be created automatically.', 'woo_mautic_integration' ),
			'desc_tip' => __( "Not recommended if you have a store with several products because will slow down processes and will requiere more server resources.", 'woo_mautic_integration' ),
			'std'      => 'no', // WooCommerce < 2.0
    		'default'  => 'no', // WooCommerce >= 2.0
			'id'       => 'mautic_woocommerce_settings_mautic_check_fields',
			'type'     => 'checkbox'
		),						

		'mautic_add_note' => array(
			'name'    => __( 'Add Notes', 'woo_mautic_integration' ),
			'desc'    => __( 'API ONLY. Add a note for every product transaction with the most important order data.', 'woo_mautic_integration' ),
		    'std'             => 'no', // WooCommerce < 2.0
		    'default'         => 'no', // WooCommerce >= 2.0	
			'id'      => 'mautic_woocommerce_settings_mautic_add_note',
			'type'    => 'checkbox'
		),

		'mautic_add_tags' => array(
			'name'            => __( 'Add Tags', 'woo_mautic_integration' ),
			'desc'            => __( "API ONLY. Add a tag with the SKU of the product (or name if don't have SKU) and the order status in the format tag_status.", 'woo_mautic_integration' ),
			'id'              => 'mautic_woocommerce_settings_mautic_add_tags',
		    'std'             => 'no', // WooCommerce < 2.0
		    'default'         => 'no', // WooCommerce >= 2.0			
			'checkboxgroup'	  => 'start',
			'show_if_checked' => 'option',
			'type'            => 'checkbox'
		),

		'mautic_add_id_to_tags' => array(
			'name'            => __( 'add_id_to_tags', 'woo_mautic_integration' ),
			'desc'            => __( 'Include the ID of the product at the beginning of the tag in the format id_tag_status.', 'woo_mautic_integration' ),
			'desc_tip'        => __( "Only work in products that doesn't have SKU.", 'woo_mautic_integration' ),
			'id'              => 'mautic_woocommerce_settings_mautic_add_id_to_tags',
			'checkboxgroup'	  => 'end',
			'show_if_checked' => 'yes',
			'type'            => 'checkbox'
		),

		'mautic_add_general_tags' => array(
			'name'        => __( 'General Tags', 'woo_mautic_integration' ),
			'desc_tip'    => __( "API ONLY. Will work only if 'Add Tags' are enabled. This will be included with every order transaction. Use text separated with commas for several tags.", 'woo_mautic_integration' ),
			'id'          => 'mautic_woocommerce_settings_mautic_add_general_tags',
			'css'         => 'min-width:200px;',
			'type'        => 'text'
		),

		'mautic_section_end' => array(
			'type' => 'sectionend',
			'id'   => 'mautic_woocommerce_settings_mautic_section_end'
		),

	);
	return apply_filters( 'mautic_woocommerce_settings', $settings );
}

// PLUGIN ADMIN FUNCTIONS

function mautic_woocommerce_admin_notice_no_configuration() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'The Mautic integration for Woocommerce needs to be configured', 'woo_mautic_integration' ); ?></p>
    </div>
    <?php
}

add_action('plugins_loaded', 'mautic_woocommerce_load_textdomain');
function mautic_woocommerce_load_textdomain() {
	load_plugin_textdomain( 'woo_mautic_integration', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}

include_once ('mautic-functions.php');

// ENABLE THE USE OF SEVERAL MAUTIC INSTANCES USING THE PRODUCT SKU AS FILTER AND SELECTOR
// CONFIGURABLE ONLY FOR CODE AND FOR ADVANCED IMPLEMENTATIONS

// $enable_multi_instance = FALSE;

function order_status_changed_action ( $order_id, $old_status, $new_status ) {

	include_once ('settings.php');

	// Get an instance of the WC_Order object
	$order = wc_get_order( $order_id );

	// Iterating through each WC_Order_Item_Product objects
	foreach ( $order->get_items() as $item_key => $item ):

		// IF PRODUCT IS NOT A SUBSCRIPTION ...
	    if ( $item->get_product()->get_type() != 'subscription' && $item->get_product()->get_type() != 'variable-subscription' ) {

			$order_data = $order->get_data(); // The Order data
		    $product    = $item->get_product(); // Get the WC_Product object

			$contact_data = array(

				'billing_first_name'   => $order_data['billing']['first_name'],
				'billing_last_name'    => $order_data['billing']['last_name'],
				'billing_company'      => $order_data['billing']['company'],
				'billing_address_1'    => $order_data['billing']['address_1'],
				'billing_address_2'    => $order_data['billing']['address_2'],
				'billing_city'         => $order_data['billing']['city'],
				'billing_postcode'     => $order_data['billing']['postcode'],
				'billing_state'        => $order_data['billing']['state'],
				'billing_country'      => $order_data['billing']['country'],
				'billing_email'        => $order_data['billing']['email'],
				'billing_phone'        => $order_data['billing']['phone'],
			);
			
			$subscription_data = array(

				'subscription_id'      => '',
				'subscription_status'  => '',

			);
			
			$subscription_data;

			$order_to_send = array(
				'order_id'             => $order_id,
				'order_parent_id'      => $order_data['parent_id'],
				'order_status'         => $new_status, 
				'order_currency'       => $order_data['currency'],
				'order_payment_method' => $order_data['payment_method'],
				'order_payment_title'  => $order_data['payment_method_title'],
			);


			$product_data = array(
			    'product_id'           => $item->get_product_id(),
				'product_name'         => $item->get_name(),
				'product_sku'          => $product->get_sku(),
				'product_type'         => $product->get_type(),
				'product_price'        => $product->get_price(), 

			);

			// SELECT THE MAUTIC INSTANCES TO USE IF THE MULTI INSTANCE IS ENABLED IF NOT THE DATA WILL BE SEND ONLY TO THE BASIC INSTANCE CONFIGURED IN THE PLUGIN SETTINGS
			$instances_to_use = mautic_instances_selector ( $enable_multi_instance, $product->get_sku(), $basic_api_settings );

			// SEND THE DATA TO EVERY ENABLED MAUTIC INSTANCE
			foreach ( $instances_to_use as $key => $instance ) {

				// SEND ALL THE ORDERS OR ONLY COMPLETED AND REFOUNDED
				if( $plugin_settings['send_not_completed_orders'] == TRUE ) {

				    // SEND ALL ORDERS TO MAUTIC
				    purchase_status_to_mautic ( $contact_data, $subscription_data, $order_to_send, $product_data, $plugin_settings, $instance );

				} else {

					// IF STATUS ORDER IS COMPLETED OR REFOUNDED SEND TO MAUTIC
					if ($order_to_send['order_status'] == 'completed' OR $order_to_send['order_status'] == 'refunded') {

						purchase_status_to_mautic ( $contact_data, $subscription_data, $order_to_send, $product_data, $plugin_settings, $instance );
					}

				}

			}




	    }

	endforeach;

}

add_action( 'woocommerce_order_status_changed', 'order_status_changed_action', 99, 3 );


function update_subscription ( $subscription, $new_status, $old_status ) {

	include_once ('settings.php');

	$related_orders_ids_array = $subscription->get_related_orders( 'all', 'parent' );

	// Iterating through each Order object (in this case only the parent order)
	foreach ( $related_orders_ids_array as $order_id ) {

	    $order = new WC_Order( $order_id );
	    $items = $order->get_items();

	    foreach ( $items as $item ) {

	    	// IF PRODUCT IS A SUBSCRIPTION ...
	    	if ( $item->get_product()->get_type() == 'subscription' OR $item->get_product()->get_type() == 'variable-subscription' ) {

		    	$order_data   = $order->get_data();   // Get the Order Data
		    	$item_data    = $item->get_data();    // Get the Item Data
			    $product      = $item->get_product(); // Get the Product Data

				$contact_data = array(

					'billing_first_name'   => $order_data['billing']['first_name'],
					'billing_last_name'    => $order_data['billing']['last_name'],
					'billing_company'      => $order_data['billing']['company'],
					'billing_address_1'    => $order_data['billing']['address_1'],
					'billing_address_2'    => $order_data['billing']['address_2'],
					'billing_city'         => $order_data['billing']['city'],
					'billing_postcode'     => $order_data['billing']['postcode'],
					'billing_state'        => $order_data['billing']['state'],
					'billing_country'      => $order_data['billing']['country'],
					'billing_email'        => $order_data['billing']['email'],
					'billing_phone'        => $order_data['billing']['phone'],
				);

				$subscription_data = array(

					'subscription_id'      => $subscription->get_id(),
					'subscription_status'  => $subscription->get_status()

				);

				$order_to_send = array(
					'order_id'             => $order_data['id'],
					'order_parent_id'      => $order_data['parent_id'],
					'order_status'         => $order_data['status'],
					'order_currency'       => $order_data['currency'],
					'order_payment_method' => $order_data['payment_method'],
					'order_payment_title'  => $order_data['payment_method_title'],
				);


				$product_to_send = array(
				    'product_id'           => $item_data['product_id'],
					'product_name'         => $item_data['name'],
					'product_type'         => $product->get_type(),
					'product_sku'          => $product->get_sku(),
					'product_price'        => $product->get_price(), 

				);

				// SELECT THE MAUTIC INSTANCES TO USE IF THE MULTI INSTANCE IS ENABLED IF NOT THE DATA WILL BE SEND ONLY TO THE BASIC INSTANCE CONFIGURED IN THE PLUGIN SETTINGS
				$instances_to_use = mautic_instances_selector ( $enable_multi_instance, $product->get_sku(), $basic_api_settings );

				// SEND THE DATA TO EVERY ENABLED MAUTIC INSTANCE
				foreach ( $instances_to_use as $key => $instance ) {

					if ( $plugin_settings['send_not_completed_orders'] == TRUE ) {

						// SEND UPDATE TO MAUTIC
						purchase_status_to_mautic ( $contact_data, $subscription_data, $order_to_send, $product_to_send, $plugin_settings, $instance );

					} else {

						if ( $subscription->get_status() == 'active' OR $subscription->get_status() == 'cancelled' ) {

							// SEND UPDATE TO MAUTIC
							purchase_status_to_mautic ( $contact_data, $subscription_data, $order_to_send, $product_to_send, $plugin_settings, $instance );

						}

					}

				}

	    	}

	    }

	}   

}

add_action('woocommerce_subscription_status_updated', 'update_subscription', 100, 3);

