=== Plugin Name ===
Contributors:
Tags: personalization, ecommerce, recommendations, cro, conversion rate optimization
Requires at least: 4.9.0
Tested up to: 5.4.1
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Personalize your ecommerce customers' experience with Personide platform 

== Description ==

Personide seamlessly integrates with your woocommerce store and makes your customers' experience a breeze by optimizing the products visibility through personalization.

== Installation ==

1. Upload and extract `personide-for-woocommerce.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Login to your personide account at app.personide.com and follow setup details

== Changelog ==

= 1.2.6 =

- Enhancement - Code cleanup, remove commented code
- Enhancement - Enable user event on frontpage and checkout pages
- Enhancement - Track checkout event on order-received page only
- Enhancement - Bring menu position next to woocommerce
- Enhancement - Reorder changelog to latest first

= 1.2.5 =

- Enhancement - Verbose checkout logging

= 1.2.4 =

- Fix - Remove woocommerce_thankyou dependency in favor of order-received query var

= 1.2.3 =

- Fix - Remove session dependency for purchase event in favor of previously used woocommerce_thankyou hook 

= 1.2.2 =

- Enhancement - Enqueue logged in user details personide tags
- Fix - Correct purchase event product id and variation id

= 1.2.1 =

- Fix - Replace events enqueue function with raw script

= 1.2.0 =

- Enhancement - Replace javascript enqueue with personide_ html tags
- Enhancement - Make Personide Connect library loading async 

= 1.1.6 =

- Enhancement - Remove hotslot template and default theme settings
- Enhancement - Remove hooks dependent hotslots

= 1.1.5 =

- Enhancement - Add category hierarchy support for category recommendations
- Fix - Handle add to cart events via hooks over javascript

= 1.1.4 =

- Enhancement - Clear events session on enqueue

= 1.1.3 =

- Fix - Use woocommerce sessions instead of php $_SESSION 

= 1.1.2 =

- Fix - Event session clearing fixed
- Enhancement - Add low level debug logs to event hook handlers

= 1.1.1 =

- Fix - Support event caching in session, change purchase event hook to wc core
- Fix - Remove recently viewed defauly placement

= 1.1.0 =

- Enhancement - Add recently viewed products on every page
- Enhancement - Add category page recommendations
- Enhancement - Add cart page recommendations