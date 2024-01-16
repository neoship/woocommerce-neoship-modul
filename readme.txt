
=== Neoship ===
Contributors: neoship
Donate link: https://neoship.sk/
Tags: neoship, shipping
Requires at least: 4.9
Tested up to: 6.2
Stable tag: 3.3.3
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Plugin integrates Neoship application in WordPress administration.


== Description ==

WooCommerce Plugin integrates Neoship application in WordPress administration. Customers can export orders to Neoship in bulk, print stickers and acceptance protocol for exported orders. Plugin adds shipping method Parcelshop. If customer
choose Parcelshop shipping method, he can also choose specific Parcelshop point.

Features:
* Export orders directly to Neoship through API
* Fill in additional data (notification options, insurance, number of packages, delivery options)
* Print stickers in multiple formats, print acceptance protocol.
* View package tracking for exported order

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/neoship` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Select Settings->Neoship screen and fill Client ID and Client Secret fields. (You obtain them, when you become a Neoship customer).
4. In case you want to set Parcelshop shipping method, select WooCommerce->Settings->Shipping, select Shipping zone (e.g. Slovakia), then->Edit. Add shipping method and set the details of shiping method selecting it.    

== Frequently Asked Questions ==

= What is Neoship? =

Neoship is expedition system. It allows make shipment more efficient by connecting to e-shops, carriers and billing systems. Customer can bulk process orders from e-shop, match cash on delivery invoices, communicate effectively with carrier, resolve claims and adjust appearance and content of notification e-mails with package tracking. Find more information on [https://neoship.sk](https://neoship.sk)

= How to become Neoship customer? =

Fill the form on [https://neoship.sk/sk/kontakt](https://neoship.sk/sk/kontakt?utm_source=wordpress&utm_medium=plugin&utm_campaign=woocommerce) or send an e-mail to [info@neoship.sk](mailto:info@neoship.sk).


== Screenshots ==

1. Settings screen
2. Export to neoship
3. Bulk actions
4. Package tracking
5. Parcelshop shipping method


== Changelog ==

= 1.0 =
* Initial release.

= 1.1 =
* Fixed bugs with backbone.js.

= 1.1.1 =
* Fixed intval() of woocommerce order id.

= 1.1.2 =
* Change reference number from order[number] to order[id]

= 1.1.3 =
* Use order number as variable number in neoship because of common custom ordering numbers by plugins (not needed change in neoship app beacuse of invoice for example).

= 1.1.4 =
* Use order number as variable number in neoship, fixed api calls with this custom numbers and not ids.

= 1.1.5 =
* Fixed parcelshop export.

= 2.0.0 =
* Add GLS support.
* Add icons to export page.
* Fix loading assets on plugin page.

= 2.0.1 =
* Fix showing carrier icons

= 2.0.2 =
* Add support COD "dobirka"

= 2.1.0 =
* Add functionality, select sticker position

= 2.2.0 =
* Add free shipping options to carriers

= 2.3.0 =
* Add SPS carrier
* Add carrier choice in export step

= 2.4.0 =
* Add shipping classes to carriers

= 3.0.0 =
* Add support for new neoship

= 3.1 =
* Add shipper packeta with fixes

= 3.1.1 =
* Fixed break in indexes of shipper

= 3.2 =
* Added shipper 123 kurier

= 3.2.1 =
* Fix response in labels printing

= 3.2.2 =
* Display error message if some labels were not printed

= 3.3.0 =
* Removed old neoship api compatibility
* Refactored actual api calls
* added DPD courier and DPD parcelshops

= 3.3.1 =
* Translations fixed

= 3.3.2 =
* Upgrade broken

== Upgrade Notice ==

= 1.0 =
Export orders to Neoship quickly and easily.
