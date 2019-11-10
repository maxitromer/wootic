# Wootic - Mautic Integration for WooCommerce

Send your Woocommerce order transactions and client data to Mautic.

Works with `Woocommerce Subscriptions`.

* You can config Wootic just with a couple of clicks to receive the data as custom fields, tags and/or notes.

* You can add by product tags and/or general tags.

* You can select if you want to receive all the orders or only the `completed` and `refunded` (`active` and `cancelled` for subscriptions).

* You can filter the orders to send using an SKU filter. 

* You can config Wootic to send the phone to Mautic or include the billing data from the checkout.

**ADVANCED**: You can even send your Woocommerce transactions to several Mautic instances filtering by product SKU (simple code requiered).

## Installation

#### Plugin installation

1. Upload the plugin files to the `/wp-content/plugins/wootic/` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Go to `Woocommerce` > `Settings` > `Mautic Integration` to set your user information and prefered options.
1. Clear your Mautic instance cache (navigate to the Mautic root folder and run `rm -rf app/cache/*`).
1. Enjoy.

#### Plugin update

Use this:

https://github.com/afragen/github-updater

