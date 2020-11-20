<?php

// COMPOSER REQUIRED LIBRARIES

require __DIR__ . '/vendor/autoload.php';

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

function send_to_mautic ( $data_to_send, $plugin_settings, $api_settings ){

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
			if ('add_id_to_tags' == TRUE) {

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
			$mt_alias_field = $data_to_send['product_sku'];

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


function order_status_changed_action( $order_id, $old_status, $new_status ){

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
	    'send_not_completed_orders' => TRUE,
	    'sku_filter'                => '',

	);

	// MAUTIC API SETTINGS

	$api_settings = array(

	    'apiURL'     => "https://example.com/", 
	    'userName'   => 'user',           // Api user       
	    'password'   => 'pass'              // Secure password

	);

	// Get an instance of the WC_Order object
	$order = wc_get_order( $order_id );

	// Iterating through each WC_Order_Item_Product objects
	foreach ($order->get_items() as $item_key => $item ):

		$order_data = $order->get_data(); // The Order data
	    $product    = $item->get_product(); // Get the WC_Product object

		$data_to_send = array(

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
			'order_id'             => $order_id,
			'order_parent_id'      => $order_data['parent_id'],
			'order_status'         => $new_status, 
			'order_currency'       => $order_data['currency'],
			'order_payment_method' => $order_data['payment_method'],
			'order_payment_title'  => $order_data['payment_method_title'],
		    'product_id'           => $item->get_product_id(),
			'product_name'         => $item->get_name(), 
			'product_sku'          => $product->get_sku(),
			'product_price'        => $product->get_price(), 

		);

		// SEND ALL THE ORDERS TO MAUTIC OR ONLY COMPLETED AND REFOUNDED
		if( $send_not_completed_orders = TRUE ) {

		    // SEND ALL ORDERS TO MAUTIC
		    send_to_mautic ( $data_to_send, $plugin_settings, $api_settings );

		} else {

			// IF STATUS ORDER IS COMPLETED OR REFOUNDED SEND TO MAUTIC
			if ($data_to_send['order_status'] == 'completed' OR $data_to_send['order_status'] == 'refounded') {

				send_to_mautic ( $data_to_send, $plugin_settings, $api_settings );
			}

		}

	endforeach;

}

add_action( 'woocommerce_order_status_changed', 'order_status_changed_action', 99, 3 );

/*

'add_fields'                => TRUE,

Will add new custom fields to every product is purchased in mautic with the value of the last order status.

'add_tags'                  => TRUE,

Will add tags with the name of the product and the order status for every order status change.

'add_id_to_tags'            => TRUE,

Enable it if you dont use SKUs for your products and need to track different products with the same name.

'add_note'                  => TRUE,

Will add a note with the order and product info for every product purchased.

'add_phone'                 => TRUE,

Will add the phone to the mautic user profile when the user make a purchase.

'add_billing'               => TRUE,

Will add all the billing info to the mautic user profile when the user make a purchase.

'use_maxmind_location'      => TRUE,

checked will leave empty the city, state and country, this is usesfull if you have enabled maxmind to find the user location in mautic.

'add_general_tags'          => 'woocommerce, test_tag_123',

Add general tags that will be populated in every user that purchase with woocommerce.

'send_not_completed_orders' => TRUE,

Enable if you want to track pending, processing, cancelled, on-hold and failed orders. If not the plugin only send completed and refounded orders to mautic.

'sku_filter'                => '',

This will filter all the orders except if the exact phrase is included in the SKU of your product. This will be helpful is you want to send only orders for a specific brand. Leave empty to send all the orders to mautic.

*/



/*
$order_id   = 123;
$old_status = 223;
$new_status = 323;

order_status_changed_action ( $order_id, $old_status, $new_status, $plugin_settings, $api_settings );
*/

/*

add_action( 'woocommerce_order_status_changed', 'order_status_changed_action', 99, 3 );

function order_status_changed_action( $order_id, $old_status, $new_status ){

	// Iterating through each WC_Order_Item_Product objects

	foreach ($order->get_items() as $item_key => $item ):

		// Get an instance of the WC_Order object
		$order      = wc_get_order( $order_id );
		$order_data = $order->get_data(); // The Order data
	 
	    $product    = $item->get_product(); // Get the WC_Product object

		$data_to_send = array(

			'billing_first_name'   => 'Jimmy',                  // $order_data['billing']['first_name'];
			'billing_last_name'    => 'Contacto',               // $order_data['billing']['last_name'];
			'billing_company'      => 'Jimmy Corp.',            // $order_data['billing']['company'];
			'billing_address_1'    => 'El Volcán 1465',         // $order_data['billing']['address_1'];
			'billing_address_2'    => 'Godoy Cruz',             // $order_data['billing']['address_2'];
			'billing_city'         => 'Mendoza',                // $order_data['billing']['city'];
			'billing_postcode'     => '5501',                   // $order_data['billing']['postcode'];
			'billing_state'        => 'Mendoza',                // $order_data['billing']['state'];
			'billing_country'      => 'Argentina',	            // $order_data['billing']['country'];
			'billing_email'        => 'maxitromer@gmail.com',   // $order_data['billing']['email'];
			'billing_phone'        => '2612574426',             // $order_data['billing']['phone'];
			'order_id'             => '12234',                  // $order_data['id'];
			'order_parent_id'      => '123',                    // $order_data['parent_id'];
			'order_status'         => 'pending',                // $order_data['status'];
			'order_currency'       => 'USD',                    // $order_data['currency'];
			'order_payment_method' => 'Credit Card',            // $order_data['payment_method'];
			'order_payment_title'  => 'VISA',                   // $order_data['payment_method_title'];
		    'product_id'           => 123,                      // $item->get_product_id(); // the Product id
			'product_name'         => 'Producto 01',            // $item->get_name();   // Name of the product
			'product_sku'          => 'OLEO_01',                // $product->get_sku(); // Product SKU
			'product_price'        => 17,                       // $product->get_price();

		);

		// SEND ALL THE ORDERS TO MAUTIC OR ONLY COMPLETED AND REFOUNDED
		if( $send_not_completed_orders = TRUE ) {

		    // SEND ALL ORDERS TO MAUTIC
		    send_to_mautic ( $data_to_send, $plugin_settings, $api_settings );

		} else {

			// IF STATUS ORDER IS COMPLETED OR REFOUNDED SEND TO MAUTIC
			if ($data_to_send['order_status'] == 'completed' OR $data_to_send['order_status'] == 'refounded') {

				send_to_mautic ( $data_to_send, $plugin_settings, $api_settings );
			}

		}

	endforeach;

}

*/







/*

// Get an instance of the WC_Order object
$order = wc_get_order( $order_id );

$order_data = $order->get_data(); // The Order data

$order_id = $order_data['id'];
$order_parent_id = $order_data['parent_id'];
$order_status = $order_data['status'];
$order_currency = $order_data['currency'];
$order_payment_method = $order_data['payment_method'];
$order_payment_method_title = $order_data['payment_method_title'];

## Creation and modified WC_DateTime Object date string ##

$order_discount_total = $order_data['discount_total'];
$order_total = $order_data['cart_tax'];

## BILLING INFORMATION:

$order_billing_first_name = $order_data['billing']['first_name'];
$order_billing_last_name = $order_data['billing']['last_name'];

$order_billing_email = $order_data['billing']['email'];
$order_billing_phone = $order_data['billing']['phone'];


// Get an instance of the WC_Order object
$order = wc_get_order($order_id);

// Iterating through each WC_Order_Item_Product objects
foreach ($order->get_items() as $item_key => $item ):

    ## Using WC_Order_Item methods ##

    // Item ID is directly accessible from the $item_key in the foreach loop or
    $item_id = $item->get_id();

    ## Using WC_Order_Item_Product methods ##

    $product      = $item->get_product(); // Get the WC_Product object
    $product_id   = $item->get_product_id(); // the Product id
    $variation_id = $item->get_variation_id(); // the Variation id
    $item_type    = $item->get_type(); // Type of the order item ("line_item")
    $item_name    = $item->get_name(); // Name of the product
    $quantity     = $item->get_quantity();  
    $line_total   = $item->get_total(); // Line total (discounted)

    // Get data from The WC_product object using methods (examples)

    $product_type   = $product->get_type();
    $product_sku    = $product->get_sku();
    $product_price  = $product->get_price();
    $stock_quantity = $product->get_stock_quantity();

endforeach;

*/

/*

if (!EL FIELD NO EXISTE) {

	# CREA EL FIELD CON EL SKU DEL PRODUCTO COMO ALIAS Y CON EL NOMBRE DEL PRODUCTO COMO NOMBRE DEL FIELD

}

# AGREGA EL ESTATUS DE LA ORDEN AL FIELD DEL PRODUCTO
# AGREGA LOS TAGS (WOOCOMMERCE, NOMBRE_DEL_PRODUCTO Y NOMBRE_DEL_PRODUCTO_STATUS)
# AGREGA TODOS LOS DATOS QUE TENGAMOS DEL CONTACTO

# ---

# CON EL ID DEL CONTACTO AGREGA TAMBIÉN UNA NOTA CON TODOS LOS DATOS DE LA ORDEN Y DE CADA PRODUCTO


/*
// CREAR O ACTUALIZAR DATOS CONTACTO (EN EL ÚLTIMO CASO SIEMPRE INCLUIR EL MAIL)

$data = array(
	'firstname' => 'Jim',
    'lastname'  => 'Contact',
    'tags' => 'test_tag_4',
    'email'     => 'maxitromer@gmail.com'
);

$result = $api->newApi("contacts", $auth, $apiUrl)->create($data);

var_dump($result);




// AGREGAR UN CAMPO A UN CONTACTO (SE AGREGA TAMBIÉN DE FORMA GLOBAL A CUSTOM FIELDS)
$id   = 3187;
$data = array(
    'label' => 'API test field',
    'type' => 'text',
);

// Create new a field of ID 3187 is not found?
$createIfNotFound = true;

// Get contact field context:
$fieldApi = $api->newApi("contactFields", $auth, $apiUrl);

$result = $fieldApi->edit($id, $data, $createIfNotFound);

var_dump($result;



// AGREGA UNA NOTA AL PERFIL DEL CONTACTO
$contactID = 3187;

$data = array(
    'lead' => $contactID,
    'text' => 'Note A',
    'type' => 'general',
);
$noteApi  = $api->newApi("notes", $auth, $apiUrl);
$note = $noteApi->create($data);



// TRAE LAS NOTAS DEL CONTACTO
$contactID = 3187;
$notes = $api->newApi("contacts", $auth, $apiUrl)->getContactNotes($contactID);
var_dump($notes);




// CREAR O ACTUALIZAR DATOS CONTACTO (EN EL ÚLTIMO CASO SIEMPRE INCLUIR EL MAIL)

$data = array(
	'firstname' => 'Jim',
    'lastname'  => 'Contact',
    'tags' => 'sku_nro_purchased, sku_nro_pending, sku_nro_cancelled', // Agrega TAGS separados por comas
    'api_test_field' => 'purchased', // Actualiza datos de campos previamente creados   
    'email'     => 'maxitromer@gmail.com'
);

$contactDATA = $api->newApi("contacts", $auth, $apiUrl)->create($data);


// AGREGA UNA NOTA AL PERFIL DEL CONTACTO
$data = array(
    'lead' => $contactDATA[contact][id],
    'type' => 'general',
    'text' => 'Order Number: 123  / Status: abc / Texto nuevo: xyz',

);

$note = $api->newApi("notes", $auth, $apiUrl)->create($data);



var_dump($contactDATA);

// print_r($contactDATA);



/////////////////////////////////////////////////////////////


// Get an instance of the WC_Order object
$order = wc_get_order( $order_id );

$order_data = $order->get_data(); // The Order data

$order_id = $order_data['id'];
$order_parent_id = $order_data['parent_id'];
$order_status = $order_data['status'];
$order_currency = $order_data['currency'];
$order_payment_method = $order_data['payment_method'];
$order_payment_method_title = $order_data['payment_method_title'];

## Creation and modified WC_DateTime Object date string ##

$order_discount_total = $order_data['discount_total'];
$order_total = $order_data['cart_tax'];

## BILLING INFORMATION:

$order_billing_first_name = $order_data['billing']['first_name'];
$order_billing_last_name = $order_data['billing']['last_name'];

$order_billing_email = $order_data['billing']['email'];
$order_billing_phone = $order_data['billing']['phone'];


// Get an instance of the WC_Order object
$order = wc_get_order($order_id);

// Iterating through each WC_Order_Item_Product objects
foreach ($order->get_items() as $item_key => $item ):

    ## Using WC_Order_Item methods ##

    // Item ID is directly accessible from the $item_key in the foreach loop or
    $item_id = $item->get_id();

    ## Using WC_Order_Item_Product methods ##

    $product      = $item->get_product(); // Get the WC_Product object
    $product_id   = $item->get_product_id(); // the Product id
    $variation_id = $item->get_variation_id(); // the Variation id
    $item_type    = $item->get_type(); // Type of the order item ("line_item")
    $item_name    = $item->get_name(); // Name of the product
    $quantity     = $item->get_quantity();  
    $line_total   = $item->get_total(); // Line total (discounted)

    // Get data from The WC_product object using methods (examples)

    $product_type   = $product->get_type();
    $product_sku    = $product->get_sku();
    $product_price  = $product->get_price();
    $stock_quantity = $product->get_stock_quantity();

endforeach;

*/






/*

foreach($fields['fields'] as $arrays) {

	echo "En este array juegan: ";

	foreach($arrays as $field) {

	echo $field ." ";
	}

	echo "<br>";

}


/*

foreach($fields['fields'] as $field)
	{
	echo $field ." ";
	}

*/
?>
