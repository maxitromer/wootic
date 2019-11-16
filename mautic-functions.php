<?php

use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;

function mautic_api ( $api_function, $api_settings ) {

	// MAUTIC API SETTINGS

	$mautic_user = array(
	    'userName'   => $api_settings['userName'], 
	    'password'   => $api_settings['password']
	);

	// INIT THE MAUTIC API

	$initAuth   = new ApiAuth();
	$auth       = $initAuth->newAuth($mautic_user, 'BasicAuth');
	$api        = new MauticApi();
	return $api->newApi( $api_function, $auth, $api_settings['apiURL'] );

}


function process_data ( $contact_data, $add_phone, $add_billing, $use_maxmind_location ) {

	// DATA PROCESSING

	$data = array(

		// 'ipAddress' => $_SERVER['REMOTE_ADDR'],
		'email'     => $contact_data['billing_email'] // REQUIERED TO ADD DATA TO A CONTACT

	);

	if ($contact_data['billing_first_name']) {

		$data['firstname'] = $contact_data['billing_first_name'];

	}

	if ($contact_data['billing_last_name']) {

		$data['lastname'] = $contact_data['billing_last_name'];

	}

	// Phone

	if ($add_phone == TRUE && $contact_data['billing_phone']) {

		$data['phone'] = $contact_data['billing_phone'];

	}

	// Billing Info

	if ($add_billing == TRUE) {

		if ($contact_data['billing_company']) {

			$data['company'] = $contact_data['billing_company'];

		}

		if ($contact_data['billing_address_1']) {

			$data['address1'] = $contact_data['billing_address_1'];

		}		

		if ($contact_data['billing_address_2']) {

			$data['address2'] = $contact_data['billing_address_2'];

		}

		if ($contact_data['billing_postcode']) {

			$data['zipcode'] = $contact_data['billing_postcode'];

		}		

		if ($use_maxmind_location == FALSE) {

			if ($contact_data['billing_city']) {

				$data['city'] = $contact_data['billing_city'];

			}

			if ($contact_data['billing_state']) {

				$data['state'] = $contact_data['billing_state'];

			}

			if ($contact_data['billing_country']) {

				$data['country'] = $contact_data['billing_country'];

			}

		}								

	}

	return $data;
}

function add_fields ( $data, $custom_fields, $check_fields, $api_enabled, $api_settings  ) {

	foreach ($custom_fields as $alias => $value) {

	    foreach ($value as $data_type => $data_value) {

			if ($check_fields == TRUE && $api_enabled == TRUE ) {

				// CHECK IF THE CUSTOM FIELD EXIST IN MAUTIC

				$fields = mautic_api( "contactFields", $api_settings )->getList(strtolower(preg_replace("[\W]", '', $alias)));

				// var_dump($fields);

				// CREATE THE CUSTOM FIELD IF NOT EXIST

				if (empty($fields['fields']) && $data_type == 'label') {

					$data_fields = array(

					    'label' => $data_value,
					    'alias' => $alias,
					    'type'  => 'text',

					);

				$field = mautic_api( "contactFields", $api_settings )->create($data_fields);

				}

			}

			// ADD CUSTOM FIELDS TO ARRAY

	    	if ($data_type == 'value') {

				$data[$alias] = $data_value;

	    	} 

	    }

	}

	return $data;

}

function add_tags ( $data, $tags ) {

	$mt_tags = '';

	for($i=0; ; $i++) {

		  $mt_tags = $mt_tags . $tags[$i];

			if ($i == count($tags)-1) {
				break;
			}

		  $mt_tags = $mt_tags . ', ';

	}

	$data['tags'] = $mt_tags;	

	return $data;

}


function push_mautic_form( $mautic_url, $data, $formId ) {


	$data['return'] = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";    
    $data['formId'] = $formId;

    $data = array('mauticform' => $data);

    $formUrl =  $mautic_url . 'form/submit?formId=' . $formId;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $formUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;     
}


function purchase_status_to_mautic ( $contact_data, $subscription_data, $order_data, $product_data, $plugin_settings, $api_settings ) {

	// FUNTION EXAMPLE

	/*

	// MAUTIC API SETTINGS

	$api_settings = array(

	    'apiURL'     => "https://localhost/mautic/", 
	    'userName'   => 'woocommerce',           // Api user       
	    'password'   => 'password'              // Secure password

	);

	// PLUGIN SETTINGS

	$plugin_settings = array(


		'enable_forms'              => TRUE, //
		'enable_api'                => TRUE, //
	    'add_fields'                => TRUE,
	    'check_fields'              => FALSE, //
	    'add_tags'                  => TRUE,
		'add_id_to_tags'            => TRUE,
	    'add_note'                  => TRUE,
	    'add_phone'                 => TRUE,
	    'add_billing'               => TRUE,
	    'use_maxmind_location'      => TRUE,
	    'add_general_tags'          => 'woocommerce, test_tag_123',
	    'send_partial_statuses'     => TRUE,
	    'sku_filter'                => '',

	);

	// AGREGATE THE DATA

	$contact_data = array(

		'billing_first_name'   => 'Jim',             // $order_data['billing']['first_name'],
		'billing_last_name'    => 'Contact',          // $order_data['billing']['last_name'],
		'billing_company'      => 'Puram',          // $order_data['billing']['company'],
		'billing_address_1'    => 'El Volcán 1465',    // $order_data['billing']['address_1'],
		'billing_address_2'    => 'Dpto. 1',           // $order_data['billing']['address_2'],
		'billing_city'         => 'Godoy Cruz',        // $order_data['billing']['city'],
		'billing_postcode'     => '5501',              // $order_data['billing']['postcode'],
		'billing_state'        => 'Mendoza',           // $order_data['billing']['state'],
		'billing_country'      => 'Argentina',         // $order_data['billing']['country'],
		'billing_email'        => 'hola19@puramura.com', // $order_data['billing']['email'],
		'billing_phone'        => '2612574426',        // $order_data['billing']['phone'],

	);

	$subscription_data = array(

		'subscription_id'      => '123 example',
		'subscription_status'  => 'active'

	);

	$order_data = array(

		'order_id'             => 3434,          // $order_id,
		'order_parent_id'      => 0101,          // $order_data['parent_id'],
		'order_status'         => 'cancelled',   // $new_status, 
		'order_currency'       => 'USD',         // $order_data['currency'],
		'order_payment_method' => 'Credit Card', // $order_data['payment_method'],
		'order_payment_title'  => 'Stripe',      // $order_data['payment_method_title'],

	);

	$product_data = array(

	    'product_id'           => 99,                    // $item->get_product_id(),
		'product_name'         => 'Tripw',               //$item->get_name(), 
		'product_type'         => 'simple',              //$product->get_type(); 
		'product_sku'          => 'OLEO_AC87-CA88_PT15', //$product->get_sku(),
		'product_price'        => 23,                    //$product->get_price(), 

	);

	purchase_status_to_mautic ( $contact_data, $subscription_data, $order_data, $product_data, $plugin_settings, $api_settings );


	*/

	// PROCESS CONTACT DATA

	$data = process_data( $contact_data, $plugin_settings['add_phone'], $plugin_settings['add_billing'], $plugin_settings['use_maxmind_location'] );

	// DECLARE REQUIERED ARRAYS

	$add_tags = array();
	$custom_fields = array();

	// CREATE THE ALIAS FIELD USED IN TAGS AND CUSTOM FIELDS

	if ($plugin_settings['add_tags'] == TRUE OR $plugin_settings['add_fields'] == TRUE) {

		// IF THE SKU IS NOT SET
		if ($product_data['product_sku'] == '') {

			// IF THE IDS ARE SET FOR TAGS AND CUSTOM FIELDS
			if ($plugin_settings['add_id_to_tags'] == TRUE) {

				// USE THE PRODUCT ID AND NAME AS ALIAS
				$alias_text = $product_data['product_id'] . '_' . $product_data['product_name'];

			} else {

				// USE THE PRODUCT NAME AS ALIAS
				$alias_text = $product_data['product_name'];

			}

			// USE THE PRODUCT ID AND NAME AS ALIAS
			$mt_alias_field = str_replace(' ', '_', strtolower(preg_replace("[\W]", '', $alias_text)));

		} else {

			// USE THE SKU AS ALIAS
			$mt_alias_field = str_replace(' ', '_', strtolower(preg_replace("[\W]", '', $product_data['product_sku'])));

		}

	}

	// CREATE THE CUSTOM FIELD

	if ($plugin_settings['add_fields'] == TRUE) {

		// HERE WE SET ONE CUSTOM FIELD TO TRACK THE ORDER STATUS OF THIS PRODUCT
		// WITH THIS WE CAN SET IN MAUTIC SEGMENTS AND CAMPAIGN PROCESSES FOR EVERY STATUS AS WE NEED

		// Custom Field Label

		$mt_alias_label = 'ID: ' . $product_data['product_id'] . ' - ' . $product_data['product_name'];

		// SET TE LABEL AND THE VALUE FOR THE CUSTOM FIELD

		$custom_fields[$mt_alias_field]['label'] = $mt_alias_label;

		// IF THE PRODUCT IS A SUBSCRIPTION ADD THE SUBSCRIPTION STATUS AS VALUE ELSE ADD THE ORDER STATUS

		if ( $product_data['product_type'] == 'subscription' OR $product_data['product_type'] == 'variable-subscription' ) {

			$custom_fields[$mt_alias_field]['value'] = $subscription_data['subscription_status'];

		} else {

			$custom_fields[$mt_alias_field]['value'] = $order_data['order_status'];
		
		}

		// INCLUDE CUSTOM FIELDS TO CONTACT DATA

		$data = add_fields ( $data, $custom_fields, $plugin_settings['add_billing'], $plugin_settings['enable_api'], $api_settings );

	}

	// IF IS ENABLED AND SKU NOT EMPTY SEND DATA TO FORM

	if (!empty( $product_data['product_sku'] ) && $plugin_settings['enable_forms'] == TRUE ) {

		$form_data = $data;

		// ADD SUBSCRIPTION DATA to FORM DATA

		$form_data['subscription_id']     = $subscription_data['subscription_id'];
		$form_data['subscription_status'] = $subscription_data['subscription_status'];

		// ADD ORDER DATA TO FORM DATA

		$form_data['order_id']             = $order_data['order_id'];
		$form_data['order_parent_id']      = $order_data['order_parent_id'];
		$form_data['order_status']         = $order_data['order_status'];
		$form_data['order_currency']       = $order_data['order_currency'];
		$form_data['order_payment_method'] = $order_data['order_payment_method'];
		$form_data['order_payment_title']  = $order_data['order_payment_title'];

		// ADD PRODUCT DATA TO FORM DATA

		$form_data['product_id']    = $product_data['product_id'];
		$form_data['product_name']  = $product_data['product_name'];
		$form_data['product_type']  = $product_data['product_type'];
		$form_data['product_sku']   = $product_data['product_sku'];
		$form_data['product_price'] = $product_data['product_price'];

		// GET THE ACTUAL STATUS OF THE PRODUCT

		$status = ( $product_data['product_type'] == 'subscription' OR $product_data['product_type'] == 'variable-subscription' ) ? $subscription_data['subscription_status'] : $order_data['order_status'] ;

		// DEFINE THE STARTS FOR EVERY ACTION

		$status_starts = array(

			'cancelled'      => 'CA',
			'pending'        => 'PE',
			'on-hold'        => 'OH',
			'failed'         => 'FA',
			'completed'      => 'CO',
			'processing'     => 'PR',
			'refunded'       => 'RE',
			'active'         => 'AC',
			'expired'        => 'EX',
			'pending-cancel' => 'PC',

		);

		// EXTRACT THE FORMS FOR EVERY ACTION IN THE SKU

		$sku_push_forms = explode("-", explode("_", $product_data['product_sku'])['1']);

		// SEARCH IN EVERY FORM ACTION

		foreach ($sku_push_forms as $key => $push_form) {
			
			// IF THE ACTION FORM STARTS WITH THE ACTUAL ORDER STATUS

			if (substr( $push_form, 0, 2 ) === $status_starts[$status]) {

				// EXTRACT THE FORM NUMBER

				$form_number = (int)substr($push_form, 2);

				// PUSH THE FORM FOR THIS ACTION

				$form_response = push_mautic_form ( $api_settings['apiURL'], $form_data, $form_number );


			}

		}

	}

	if ($plugin_settings['enable_api'] == TRUE) {

		// CONSTRUCT THE MAUTIC TAG TEXT DATA

		if ($plugin_settings['add_tags'] == TRUE) {

			// ADD THE TAG AND THE STATUS FOR THIS PRODUCT

			// IF THE PRODUCT IS A SUBSCRIPTION ADD THE SUBSCRIPTION STATUS AS A TAG PART ELSE ADD THE ORDER STATUS

			if ( $product_data['product_type'] == 'subscription' OR $product_data['product_type'] == 'variable-subscription' ) {

				$add_tags[] = $mt_alias_field . '_' . $subscription_data['subscription_status'];

			} else {

				$add_tags[] = $mt_alias_field . '_' . $order_data['order_status'];
				
			}

			// IF YOU HAVE, ADD THE GENERAL TAGS

			if ($plugin_settings['add_general_tags'] !== '') {

				$general_tags = explode(',', $plugin_settings['add_general_tags']);

				foreach($general_tags as $i) {

					$add_tags[] = trim($i);
					
				}

			}

			// INCLUDE TAGS TO CONTACT DATA

			$data = add_tags ( $data, $add_tags );

		} 

		// UPDATE THE CONTACT IN MAUTIC

		$contact = mautic_api ( "contacts", $api_settings )->create($data);


		if ($plugin_settings['add_note'] == TRUE) {

			// CONSTRUCT THE MAUTIC NOTE TEXT FIELD

			$product_status = ( $product_data['product_type'] == 'subscription' OR $product_data['product_type'] == 'variable-subscription' ) ? $subscription_data['subscription_status'] : $order_data['order_status'] ;

			$mt_note = 'Product: ' . 'ID: ' . $product_data['product_id'] . ' - ' . $product_data['product_name'] . ' / Product Type: ' . $product_data['product_type'] . ' / Product SKU: ' . $product_data['product_sku'] . ' / Product Price: ' . $product_data['product_price'] . '  / Status: ' . $product_status . ' / Order Number: ' . $order_data['order_id'] . '  / Order Parent ID: ' . $order_data['order_parent_id'] . '  / Payment Method: ' . $order_data['order_payment_method'] . '  / Payment Method Title: ' . $order_data['order_payment_title'] . '  / Payment Currency: ' . $order_data['order_currency'];

			// AGREGATE THE NOTE DATA

			$data_note = array(

			    'lead'     => $contact['contact']['id'],
			    'type'     => 'general',
			    'text'     => $mt_note,
			    'dateTime' => gmdate("Y-m-d\TH:i:s\Z"),

			);

			// ADD NOTE TO THE CONTACT

			$note = mautic_api ( "notes", $api_settings )->create($data_note);

		}

	}

}	













function contact_to_mautic_old ( $contact_data, $contact_settings, $api_settings, $tags, $custom_fields ) {

    /* EXAMPLE DATA

	// CONTACT FUNCTION CONFIG

	$contact_settings = array(

		'add_tags'             => TRUE,
		'add_phone'            => TRUE, 
		'add_billing'          => TRUE,
		'use_maxmind_location' => TRUE,
		'add_fields'           => TRUE,

	);

	// MAUTIC API SETTINGS

	$api_settings = array(

	    'apiURL'     => "https://localhost/mautic/", 
	    'userName'   => 'woocommerce',           // Api user       
	    'password'   => 'password'              // Secure password

	);

	// AGREGATE ALL THE CONTACT DATA

	$contact_data = array(

		'billing_first_name'   => 'Jimmy',             // $order_data['billing']['first_name'],
		'billing_last_name'    => 'Contacto',          // $order_data['billing']['last_name'],
		'billing_company'      => 'Puramura',          // $order_data['billing']['company'],
		'billing_address_1'    => 'El Volcán 1465',    // $order_data['billing']['address_1'],
		'billing_address_2'    => 'Dpto. 1',           // $order_data['billing']['address_2'],
		'billing_city'         => 'Godoy Cruz',        // $order_data['billing']['city'],
		'billing_postcode'     => '5501',              // $order_data['billing']['postcode'],
		'billing_state'        => 'Mendoza',           // $order_data['billing']['state'],
		'billing_country'      => 'Argentina',         // $order_data['billing']['country'],
		'billing_email'        => 'hola@puramura.com', // $order_data['billing']['email'],
		'billing_phone'        => '2612574426',        // $order_data['billing']['phone'],

	);

	$add_tags = array( 'mautic', 'test_woo_tag', 'new_test_tag' );


	$custom_fields = array(

		'alias_data_8' => array(
			'label' => 'Label Data 9',
			'value' => 'value_data_999999999'
		),
		'alias_data_5' => array(
			'label' => 'Label Data 1',
			'value' => 'value_data_1'
		),
		'alias_data_4' => array(
			'label' => 'Label Data 2',
			'value' => 'value_data_2'
		)		

	);

	*/


	// DATA PROCESSING

	$data = array(

		'ipAddress' => $_SERVER['REMOTE_ADDR'],
		'email'     => $contact_data['billing_email'] // REQUIERED TO ADD DATA TO A CONTACT

	);

	if ($contact_data['billing_first_name']) {

		$data['firstname'] = $contact_data['billing_first_name'];

	}

	if ($contact_data['billing_last_name']) {

		$data['lastname'] = $contact_data['billing_last_name'];

	}

	// Phone

	if ($contact_settings['add_phone'] == TRUE && $contact_data['billing_phone']) {

		$data['phone'] = $contact_data['billing_phone'];

	}

	// Billing Info

	if ($contact_settings['add_billing'] == TRUE) {

		if ($contact_data['billing_company']) {

			$data['company'] = $contact_data['billing_company'];

		}

		if ($contact_data['billing_address_1']) {

			$data['address1'] = $contact_data['billing_address_1'];

		}		

		if ($contact_data['billing_address_2']) {

			$data['address2'] = $contact_data['billing_address_2'];

		}

		if ($contact_data['billing_postcode']) {

			$data['zipcode'] = $contact_data['billing_postcode'];

		}		

		if ($contact_settings['use_maxmind_location'] == FALSE) {

			if ($contact_data['billing_city']) {

				$data['city'] = $contact_data['billing_city'];

			}

			if ($contact_data['billing_state']) {

				$data['state'] = $contact_data['billing_state'];

			}

			if ($contact_data['billing_country']) {

				$data['country'] = $contact_data['billing_country'];

			}

		}								

	}

	// IF CUSTOM FIELDS ARE ENABLED

	if ($contact_settings['add_fields'] == TRUE && $custom_fields) {

		foreach ($custom_fields as $alias => $value) {

		    foreach ($value as $data_type => $data_value) {

				// CHECK IF THE CUSTOM FIELD EXIST IN MAUTIC

				$fields = mautic_api( "contactFields", $api_settings )->getList(strtolower(preg_replace("[\W]", '', $alias)));

				// var_dump($fields);

				// CREATE THE CUSTOM FIELD IF NOT EXIST

				if (empty($fields['fields']) && $data_type == 'label') {

					$data_fields = array(

					    'label' => $data_value,
					    'alias' => $alias,
					    'type'  => 'text',

					);

				$field = mautic_api( "contactFields", $api_settings )->create($data_fields);

				}

				// ADD CUSTOM FIELDS TO ARRAY

		    	if ($data_type == 'value') {

		    		// echo "{$alias} has a data type named: {$data_type} with the value: {$data_value} $";
		    		// echo "<br>";

					$data[$alias] = $data_value;

		    	} 

		    }

		}

	}

	if ($contact_settings['add_tags'] == TRUE && $tags) {

		$mt_tags = '';

		for($i=0; ; $i++) {

			  $mt_tags = $mt_tags . $tags[$i];

				if ($i == count($tags)-1) {
					break;
				}

			  $mt_tags = $mt_tags . ', ';

		}

		$data['tags'] = $mt_tags;

	}

	// var_dump($data);

	// UPDATE THE CONTACT IN MAUTIC

	$contact = mautic_api ( "contacts", $api_settings )->create($data);

	return $contact;

}

function mautic_instances_selector ( $enable_multi_instance, $product_sku, $basic_api_settings ) {

	/*

	EXAMPLE DATA:
	-------------

	$basic_api_settings = array(

	    'apiURL'     => "https://localhost/mautic/", 
	    'userName'   => 'woocommerce',      
	    'password'   => 'password', 
	    'sku_filter' => ''             

	);

	$enable_multi_instance = TRUE;

	$product_sku = 'O';

	$instances_to_use = mautic_instances_selector ( $enable_multi_instance, $product_sku, $basic_api_settings );

	foreach ( $instances_to_use as $key => $instance ) {

		// Run your code ...

		// var_dump( $instance );

	}

	*/

	if ( $enable_multi_instance == TRUE ) {

		include_once ('multi-instance.php');

		$mautic_instances[] = $basic_api_settings;

		foreach($mautic_instances as $key => $instance) {

			// IF SKU_FILTER IS EMPTY OR THE SKU_FILTER CONTENT IS INCLUDED IN THE PRODUCT SKU

			if ( $product_sku == '' OR $instance['sku_filter'] == '' OR strpos($product_sku, $instance['sku_filter']) !== false ) {

				$add_instance['apiURL']   = $instance['apiURL'];
				$add_instance['userName'] = $instance['userName'];
				$add_instance['password'] = $instance['password'];

				$instances_to_use[] = $add_instance;

			}
			
		}

	} else {


		if ( $product_sku == '' OR $basic_api_settings['sku_filter'] == '' OR strpos($product_sku, $basic_api_settings['sku_filter']) !== false ) {

			$instances_to_use[] = $basic_api_settings;

		}

	}

	return $instances_to_use;
}

function purchase_status_to_mautic_old ( $contact_data, $subscription_data, $order_data, $product_data, $plugin_settings, $api_settings ) {

	/* EXAMPLE DATA
	// MAUTIC API SETTINGS

	$api_settings = array(

	    'apiURL'     => "https://localhost/mautic/", 
	    'userName'   => 'woocommerce',           // Api user       
	    'password'   => 'password'              // Secure password

	);

	// PLUGIN SETTINGS

	$plugin_settings = array(

	    'add_fields'                => TRUE,
	    'add_tags'                  => TRUE,
		'add_id_to_tags'            => TRUE,
	    'add_note'                  => TRUE,
	    'add_phone'                 => TRUE,
	    'add_billing'               => TRUE,
	    'use_maxmind_location'      => TRUE,
	    'add_general_tags'          => 'woocommerce, test_tag_123',
	    'send_partial_statuses'     => TRUE,
	    'sku_filter'                => '',

	);

	// AGREGATE THE DATA

	$contact_data = array(

		'billing_first_name'   => 'Jimmy',             // $order_data['billing']['first_name'],
		'billing_last_name'    => 'Contacto',          // $order_data['billing']['last_name'],
		'billing_company'      => 'Puramura',          // $order_data['billing']['company'],
		'billing_address_1'    => 'El Volcán 1465',    // $order_data['billing']['address_1'],
		'billing_address_2'    => 'Dpto. 1',           // $order_data['billing']['address_2'],
		'billing_city'         => 'Godoy Cruz',        // $order_data['billing']['city'],
		'billing_postcode'     => '5501',              // $order_data['billing']['postcode'],
		'billing_state'        => 'Mendoza',           // $order_data['billing']['state'],
		'billing_country'      => 'Argentina',         // $order_data['billing']['country'],
		'billing_email'        => 'hola@puramura.com', // $order_data['billing']['email'],
		'billing_phone'        => '2612574426',        // $order_data['billing']['phone'],

	);

	$subscription_data = array(

		'subscription_id'      => '123 example',
		'subscription_status'  => 'active'

	);

	$order_data = array(

		'order_id'             => 3434,          // $order_id,
		'order_parent_id'      => 0101,          // $order_data['parent_id'],
		'order_status'         => 'completed',   // $new_status, 
		'order_currency'       => 'USD',         // $order_data['currency'],
		'order_payment_method' => 'Credit Card', // $order_data['payment_method'],
		'order_payment_title'  => 'Stripe',      // $order_data['payment_method_title'],

	);

	$product_data = array(

	    'product_id'           => 99,             // $item->get_product_id(),
		'product_name'         => 'Tripw',        //$item->get_name(), 
		'product_type'         => 'subscription', //$product->get_type(); 
		'product_sku'          => '',             //$product->get_sku(),
		'product_price'        => 23,             //$product->get_price(), 

	);

	purchase_status_to_mautic ( $contact_data, $subscription_data, $order_data, $product_data, $plugin_settings, $api_settings );

	*/

	// CONTACT FUNCTION CONFIG

	$contact_settings = array(

		'add_tags'             => $plugin_settings['add_tags'],
		'add_phone'            => $plugin_settings['add_phone'], 
		'add_billing'          => $plugin_settings['add_billing'],
		'use_maxmind_location' => $plugin_settings['use_maxmind_location'],
		'add_fields'           => $plugin_settings['add_fields'],
		'check_fields'         => TRUE,

	);


	// DECLARE REQUIERED ARRAYS

	$add_tags = array();
	$custom_fields = array();

	// CREATE THE ALIAS FIELD USED IN TAGS AND CUSTOM FIELDS

	if ($plugin_settings['add_tags'] == TRUE OR $plugin_settings['add_fields'] == TRUE) {

		// IF THE SKU IS NOT SET
		if ($product_data['product_sku'] == '') {

			// IF THE IDS ARE SET FOR TAGS AND CUSTOM FIELDS
			if ($plugin_settings['add_id_to_tags'] == TRUE) {

				// USE THE PRODUCT ID AND NAME AS ALIAS
				$alias_text = $product_data['product_id'] . '_' . $product_data['product_name'];

			} else {

				// USE THE PRODUCT NAME AS ALIAS
				$alias_text = $product_data['product_name'];

			}

			// USE THE PRODUCT ID AND NAME AS ALIAS
			$mt_alias_field = str_replace(' ', '_', strtolower(preg_replace("[\W]", '', $alias_text)));

		} else {

			// USE THE SKU AS ALIAS
			$mt_alias_field = str_replace(' ', '_', strtolower(preg_replace("[\W]", '', $product_data['product_sku'])));

		}

		// CREATE THE CUSTOM FIELD

		if ($plugin_settings['add_fields'] == TRUE) {

			// HERE WE SET ONE CUSTOM FIELD TO TRACK THE ORDER STATUS OF THIS PRODUCT
			// WITH THIS WE CAN SET IN MAUTIC SEGMENTS AND CAMPAIGN PROCESSES FOR EVERY STATUS AS WE NEED

			// Custom Field Label

			$mt_alias_label = 'ID: ' . $product_data['product_id'] . ' - ' . $product_data['product_name'];

			// SET TE LABEL AND THE VALUE FOR THE CUSTOM FIELD

			$custom_fields[$mt_alias_field]['label'] = $mt_alias_label;

			// IF THE PRODUCT IS A SUBSCRIPTION ADD THE SUBSCRIPTION STATUS AS VALUE ELSE ADD THE ORDER STATUS

			if ( $product_data['product_type'] == 'subscription' OR $product_data['product_type'] == 'variable-subscription' ) {

				$custom_fields[$mt_alias_field]['value'] = $subscription_data['subscription_status'];

			} else {

				$custom_fields[$mt_alias_field]['value'] = $order_data['order_status'];
			
			}

			// var_dump($custom_fields);
		}


		// CONSTRUCT THE MAUTIC TAG TEXT DATA

		if ($plugin_settings['add_tags'] == TRUE) {

			// ADD THE TAG AND THE STATUS FOR THIS PRODUCT

			// IF THE PRODUCT IS A SUBSCRIPTION ADD THE SUBSCRIPTION STATUS AS A TAG PART ELSE ADD THE ORDER STATUS

			if ( $product_data['product_type'] == 'subscription' OR $product_data['product_type'] == 'variable-subscription' ) {

				$add_tags[] = $mt_alias_field . '_' . $subscription_data['subscription_status'];

			} else {

				$add_tags[] = $mt_alias_field . '_' . $order_data['order_status'];
				
			}

			// IF YOU HAVE, ADD THE GENERAL TAGS
			if ($plugin_settings['add_general_tags'] !== '') {

				$general_tags = explode(',', $plugin_settings['add_general_tags']);

				foreach($general_tags as $i) {

					$add_tags[] = trim($i);
					
				}

			}

		} 

	}

	$contact = contact_to_mautic ( $contact_data, $contact_settings, $api_settings, $add_tags, $custom_fields );

	if ($plugin_settings['add_note'] == TRUE) {

		// CONSTRUCT THE MAUTIC NOTE TEXT FIELD

		$product_status = ( $product_data['product_type'] == 'subscription' OR $product_data['product_type'] == 'variable-subscription' ) ? $subscription_data['subscription_status'] : $order_data['order_status'] ;

		$mt_note = 'Product: ' . 'ID: ' . $product_data['product_id'] . ' - ' . $product_data['product_name'] . ' / Product Type: ' . $product_data['product_type'] . ' / Product SKU: ' . $product_data['product_sku'] . ' / Product Price: ' . $product_data['product_price'] . '  / Status: ' . $product_status . ' / Order Number: ' . $order_data['order_id'] . '  / Order Parent ID: ' . $order_data['order_parent_id'] . '  / Payment Method: ' . $order_data['order_payment_method'] . '  / Payment Method Title: ' . $order_data['order_payment_title'] . '  / Payment Currency: ' . $order_data['order_currency'];

		// AGREGATE THE NOTE DATA

		$data_note = array(

		    'lead'     => $contact['contact']['id'],
		    'type'     => 'general',
		    'text'     => $mt_note,
		    'dateTime' => gmdate("Y-m-d\TH:i:s\Z"),

		);

		// ADD NOTE TO THE CONTACT

		$note = mautic_api ( "notes", $api_settings )->create($data_note);

	}

}

function order_to_mautic_old ( $data_to_send, $plugin_settings, $api_settings ){

	// IF SKU_FILTER IS EMPTY OR THE SKU_FILTER CONTENT IS INCLUDED IN THE PRODUCT SKU
	if ($plugin_settings['sku_filter'] == '' OR strpos($data_to_send['product_sku'], $plugin_settings['sku_filter']) !== false) {

		# SEND THE DATA TO MAUTIC

		// DATA PROCESSING

		//Requiered Info

		$mt_firstname                 = $data_to_send['billing_first_name'];
		$mt_lastname                  = $data_to_send['billing_last_name'];
		$mt_email                     = $data_to_send['billing_email'];

		// Phone

		$mt_phone = ($plugin_settings['add_phone'] == TRUE) ? $data_to_send['billing_phone'] : '' ;

		// Billing Info

		if ($plugin_settings['add_billing'] == TRUE) {

			$mt_company                   = $data_to_send['billing_company'];
			$mt_address_1                 = $data_to_send['billing_address_1'];
			$mt_address_2                 = $data_to_send['billing_address_2'];
			$mt_zipcode                   = $data_to_send['billing_postcode'];

			if ($plugin_settings['use_maxmind_location'] == TRUE) {

				$mt_city = $mt_country = $mt_state = '';

			} else {

				$mt_city                      = $data_to_send['billing_city'];
				$mt_state                     = $data_to_send['billing_state'];
				$mt_country                   = $data_to_send['billing_country'];

			}

		} else  {

			$mt_company = $mt_address_1 = $mt_address_2 = $mt_city = $mt_zipcode = $mt_country = $mt_state = '';

		}

		// Order Info

		$mt_order_id                  = $data_to_send['order_id'];
		$mt_order_status              = $data_to_send['order_status'];
		$mt_order_payment_method      = $data_to_send['order_payment_method'];
		$mt_order_payment_title       = $data_to_send['order_payment_title'];
		$mt_order_parent_id           = $data_to_send['order_parent_id'];
		$mt_order_currency            = $data_to_send['order_currency'];

		// Product Info

		$mt_product_name              = $data_to_send['product_name'];
		$mt_product_id                = $data_to_send['product_id'];
		$mt_product_sku               = $data_to_send['product_sku'];
		$mt_product_price             = $data_to_send['product_price'];

		// Note Label

		$mt_alias_label               = 'ID: ' . $data_to_send['product_id'] . ' - ' . $data_to_send['product_name'];

		// Note Alias

		// IF THE SKU IS NOT SET
		if ($data_to_send['product_sku'] == '') {

			// IF THE IDS ARE SET FOR TAGS AND CUSTOM FIELDS
			if ($plugin_settings['add_id_to_tags'] == TRUE) {

				// USE THE PRODUCT ID AND NAME AS ALIAS
				$alias_text = $data_to_send['product_id'] . '_' . $data_to_send['product_name'];

			} else {

				// USE THE PRODUCT NAME AS ALIAS
				$alias_text = $data_to_send['product_name'];

			}

			// USE THE PRODUCT ID AND NAME AS ALIAS
			$mt_alias_field = str_replace(' ', '_', strtolower(preg_replace("[\W]", '', $alias_text)));

		} else {

			// USE THE SKU AS ALIAS
			$mt_alias_field = str_replace(' ', '_', strtolower(preg_replace("[\W]", '', $data_to_send['product_sku'])));

		}

		if ($plugin_settings['add_fields'] == TRUE) {

			// CHECK IF THE CUSTOM FIELD EXIST IN MAUTIC

			$fields = mautic_api( "contactFields", $api_settings )->getList(strtolower(preg_replace("[\W]", '', $mt_alias_field)));

			// CREATE THE CUSTOM FIELD IF NOT EXIST

			if (empty($fields['fields'])) {

				$data_fields = array(

				    'label' => $mt_alias_label,
				    'alias' => $mt_alias_field,
				    'type'  => 'text',

				);

			$field = mautic_api( "contactFields", $api_settings )->create($data_fields);

			}

		}

		// UPDATE THE CONTACTS DATA INCLUDING THE CUSTOM FIELD FOR THIS PRODUCT AND THE TAGS (IF ENABLED)

		if ($plugin_settings['add_tags'] == TRUE) {

			// CONSTRUCT THE MAUTIC TAG TEXT DATA

			if ($plugin_settings['add_general_tags'] == '') {

				$mt_tags = $mt_alias_field . '_' . $mt_order_status;

			}

			$mt_tags = $plugin_settings['add_general_tags'] . ', ' . $mt_alias_field . '_' . $mt_order_status;

		} else {

			// TAGS ARE EMPTY

			$mt_tags ='';

		}

		// AGREGATE ALL THE CONTACT DATA

		$data_contact = array(
			'firstname'     => $mt_firstname,
			'lastname'      => $mt_lastname,
			'email'         => $mt_email,
			'company'       => $mt_company,
			'address1'      => $mt_address_1,
			'address2'      => $mt_address_2,
			'city'          => $mt_city,
			'state'         => $mt_state,
			'country'       => $mt_country,
			'zipcode'       => $mt_zipcode,	
			'phone'         => $mt_phone,
			'tags'          => $mt_tags,
			$mt_alias_field => $mt_order_status
		);

		// UPDATE THE CONTACT DATA

		$contact = mautic_api ( "contacts", $api_settings )->create($data_contact);

		// var_dump($contact);

		// ADD A NOTE WITH THE ORDER AND PRODUCT DATA (IF ENABLED)

		if ($plugin_settings['add_note'] == TRUE) {

			// CONSTRUCT THE MAUTIC NOTE TEXT FIELD

			$mt_note = 'Order Number: ' . $mt_order_id . '  / Status: ' . $mt_order_status . '  / Order Parent ID: ' . $mt_order_parent_id . '  / Payment Method: ' . $mt_order_payment_method . '  / Payment Method Title: ' . $mt_order_payment_title . '  / Payment Currency: ' . $mt_order_currency . ' / Product: ' . 'ID: ' . $mt_product_id . ' - ' . $mt_product_name . ' / Product SKU: ' . $mt_product_sku . ' / Product Price: ' . $mt_product_price;

			// AGREGATE THE NOTE DATA

			$data_note = array(

			    'lead'     => $contact['contact']['id'],
			    'type'     => 'general',
			    'text'     => $mt_note,
			    'dateTime' => gmdate("Y-m-d\TH:i:s\Z"),

			);

			// ADD NOTE TO THE CONTACT

			$note = mautic_api ( "notes", $api_settings )->create($data_note);

		}

	}

}