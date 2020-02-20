# Wootic - Mautic Integration for WooCommerce



**The FASTER PLUGIN to send your Woocommerce order transactions and client data to Mautic.**


Integrated with `Woocommerce Subscriptions` too.


## What you can do with Wootic?


* You can config Wootic just with a couple of clicks to receive the data in forms, custom fields, tags and/or notes.

* You can add by product tags and/or general tags.

* You can select if you want to receive all the partial order status or only the `completed` and `refunded` (`active` and `cancelled` for subscriptions).

* You can filter the orders to send using an SKU filter. 

* You can config Wootic to send the client phone to Mautic or include all the billing data from the checkout.

**ADVANCED** 

* You can even config several Mautic instances to receive your Woocommerce transactions filtering by product SKU (simple code requiered).

You can read more about this and how to config it in the `multi-instance.php` file.


## How to Install it


1. Upload the plugin files to the `/wp-content/plugins/wootic/` directory, or install the plugin through the WordPress plugins screen directly. Activate through the 'Plugins' screen in WordPress.

1. Go to `Woocommerce` > `Settings` > `Mautic Integration` to set your user information.

1. Clear your Mautic instance cache (using CLI navigate to the Mautic root folder and run `rm -rf app/cache/*`).



## How to Configure it


**IMPORTANT: Please read this full documentation to really understand how to config Wootic correctly and leaverage all the power of this plugin in your site.**

This plugin have 2 integration methods.

The API method used in every plugin outhere and the form method.

You can use one or both methods at the same time to send the information for different channels (not recommended but posible).

The API method is simpler to use and have more options and tools.

The FORM method is faster and more flexible but more complex to config and use.



### How to use Wootic with the API method? 

1. Enable the API with the basic auth method.

Here is how to do it:

![mautic-api-settings](https://user-images.githubusercontent.com/6311835/73707860-6be25a80-46db-11ea-8867-247e043ce035.png)

2. Create a user and a SECURE password just used for this woocommerce integration and set it in `Woocommerce` > `Settings` > `Mautic Integration`.

3. Clear your Mautic instance cache (navigate to the Mautic root folder and run `rm -rf app/cache/*`). and enjoy :-)

You will receive all the selected order transactions in custom fields, notes or tags as you specify in the plugin options.

With this method the plugin automatically create and update everything for you in Mautic.

This works amaizing if you have a business with just a couple of products and s small list of clients, for example a B"B business.

The caveat here is that this method is simpler but slower.



### How to use Wootic with the FORM method? 


After testing alternatives we find that **sending the info using forms is 2X faster** thats why we add this new Form Method.

This is usesful if you have or plan to have a big list of contacts in Mautic or several campaigns and segment filters that could slowdown your instance.

You are not be able to receive `tags` or `notes`, only forms ... 

... but you can fire that tags or notes using a campaign when that form is completed.

With this method you will need to create and config the Mautic forms for every order status you want to receive.

You can use only one form to receive all your products data or clone that form for every product or every type of trasanction.

This makes extremelly flexible the plugin and you can config everything as your business requiere.

You will need to add a special SKU to every product too to set the order status and form number you want to receive your data into mautic.

3 requiered steps to make this method work:

1. Enable the Forms Integration method in the plugin settings.

1. Set the requiered forms in Mautic.

1. Set the requiered products SKU.


#### How to set this forms in Mautic?


With this fields:

* firstname
* lastname
* email
* phone
* company
* address1
* address2
* zipcode
* city
* state
* country
* order_id
* order_parent_id
* order_status
* order_currency
* order_payment_method
* order_payment_title
* subscription_id
* subscription_status
* product_id
* product_name
* product_type
* product_sku
* product_price

Every field you config like this will be populated with data from the transaction order.


#### How must be set the product SKU in your products?


Like this:

`BRAND_CO87-PR8-OH19-RE87-PE87-AC3-CA88_PT15`

As you can see that SKU have 3 parts divided by `_`.

`BRAND`  `_`  `CO87-PR8-OH19-RE87-PE87-AC3-CA88`  `_`  `PT15`

The first part could be used for the SKU filter of the plugin, in this case the `BRAND` part.

The second part is a series of ACTION FORMS that will be executed only when the specific status order will be created or modified.

This action forms are divided by `-`.

One example of an action form that appears here is `CO87`.

The first 2 letters are the order status code.

This is the full list of codes for every order action:


* `CA` > `cancelled`
* `PE` > `pending`
* `OH` > `on-hold`
* `FA` > `failed`
* `CO` > `completed`
* `PR` > `processing`
* `RE` > `refunded`
* `AC` > `active`
* `EX` > `expired`
* `PC` > `pending-cancel`


The last number is the ID of the Mautic form.

This action `CO87` will fire when an order has the status of `completed` and will send the information to the Mautic form with the ID `87`.

Yes, you can add all the ACTION FORMS you need.

Yes, you can repeat order codes and/or form IDs and will be fired.

The 3rd part (`PT15` in this example) could be used for the SKU itself with your product information ( `JEAN-235544` ) or could be blank if you dont use SKU for your products.

That's all you need to know to start with Wootic.


### How to track abandoned Carts?


You can use Wootic to track and send mails to `pending`, `on-hold` or `failed` orders.

If you want to track abandoned carts BEFORE the order is placed you can use this:

https://wordpress.org/plugins/woo-cart-abandonment-recovery/ (FREE)

https://wordpress.org/plugins/wp-marketing-automations/ (FREE)


### How to update Wootic?


Use this:

https://github.com/afragen/github-updater


### New Ideas / Questions / Contributing / Bugs


Check the GitHub issues if it is already listed in there; if not, write IN A DETAILED AND COMPRENSIVE WAY in the `Issues` section so I can help you better.

