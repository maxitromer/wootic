# Wootic - Mautic Integration for WooCommerce

The FASTER PLUGIN to send your Woocommerce order transactions and client data to Mautic.

Integrated with `Woocommerce Subscriptions` too.

* You can config Wootic just with a couple of clicks to receive the data in forms, custom fields, tags and/or notes.

* You can add by product tags and/or general tags.

* You can select if you want to receive all the partial order status or only the `completed` and `refunded` (`active` and `cancelled` for subscriptions).

* You can filter the orders to send using an SKU filter. 

* You can config Wootic to send the client phone to Mautic or include all the billing data from the checkout.

**ADVANCED**: You can even config several Mautic instances to receive your Woocommerce transactions filtering by product SKU (simple code requiered).


## Installation


#### Plugin installation

1. Upload the plugin files to the `/wp-content/plugins/wootic/` directory, or install the plugin through the WordPress plugins screen directly. Activate through the 'Plugins' screen in WordPress.
1. Go to `Woocommerce` > `Settings` > `Mautic Integration` to set your user information.
1. Clear your Mautic instance cache (using CLI navigate to the Mautic root folder and run `rm -rf app/cache/*`).


#### How to Configure it

This plugin have 2 integration methods.

The API method used in every plugin outhere and the form method.

You can use the best for your requierements or use both to send the information for different channels (not recommended but posible).

The API method is more simple, have more options and makes more simple the configuration.

How to config Wootic with this method? 

You just have to create a user and a SECURE password for the woocommerce integration and set it in `Woocommerce` > `Settings` > `Mautic Integration`.

Then simply select your prefered options and enjoy.

You will receive all the selected order transactions in custom fields, notes or tags as you specify in the plugin options.

With this method the plugin automatically create and update everything for you in Mautic.

This works amaizing if you have a business with just a couple of products and s small list of clients, for example a B"B business.

The caveat here is that this method is simpler but slower.

Thats why after testing alternatives we find that sending the info using forms is 2X faster and add the new Form Method to send data to Mautic.

This is usesful if you have or plan to have a big list of contacts in Mautic or several campaigns and segment filters that could slowdown your instance.

You are not be able to receive `tags` or `notes`, only forms but you can fire that tags or notes using a campaign when that form is completed.

With this method you will need to create and config the Mautic forms for every order status you want to receive.

You can use only one form to receive all your products data or clone that form for every product or every type of trasanction.

This makes extremelly flexible the plugin and you can config everything as your business requiere.

You will need to add a special SKU to every product too to set the order status and form number you want to receive your data into mautic.

How to config this forms in Mautic?

With this fields:

* firstname
* lastname
* email
* phone
* order_id
* order_parent_id
* order_status
* order_currency
* order_payment_method
* order_payment_title
* product_id
* product_name
* product_type
* product_sku
* product_price

Every field you config like this will be populated with data from the transaction order.

How must be config the product SKU in your products?

Like this:

`BRAND_CO87-PR87-OH87-RE87-PE87-AC87-CA88_PT15`

As you can see that SKU have 3 parts divided by `_`.

`BRAND` `_` `CO87-PR87-OH87-RE87-PE87-AC87-CA88` `_` `PT15`

The first part could be used for the SKU filter of the plugin, in this case the `BRAND` part.

The second part is a series of ACTION FORMS that will be executed only when the specific status order will be created or modified.

This action forms are divided by `-`.

One example of an action form that appears here is `CO87`.

The first 2 letters are the order status code.

This is the full list of codes for every order action:

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

The last number is the ID of the Mautic form.

This action `CO87` will fire when an order has the status of `completed` and will send the information to the Mautic form with the ID `87`.

That's all you need to know to start with Wootic.


#### How to update Wootic?

Use this:

https://github.com/afragen/github-updater


#### Questions, Ideas or Problems

If you have questions, find a bug or have a new idea for the plugin just write it in the `issues` section.



