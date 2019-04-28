=== Plugin Name ===
Contributors:
Tags: personalization, ecommerce, recommendations, cro, conversion rate optimization
Requires at least: 4.9.0
Tested up to: 4.9.6
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Personalize your ecommerce customers' experience with Personide platform 

== Description ==

Personide seamlessly integrates with your woocommerce store and makes your customers' experience a breeze by optimizing the products visibility through personalization.

== Installation ==

1. Upload and extract `personide-for-woocommerce.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Login to your personide account and follow setup details

== Changelog ==

= 1.1.0 =

- Enhancement - Add recently viewed products on every page
- Enhancement - Add category page recommendations
- Enhancement - Add cart page recommendations

= 1.1.1 =

- Fix - Support event caching in session, change purchase event hook to wc core
- Fix - Remove recently viewed defauly placement

= 1.1.2 =

- Fix - Event session clearing fixed
- Enhancement - Add low level debug logs to event hook handlers

= 1.1.3 =

- Fix - Use woocommerce sessions instead of php $_SESSION 

= 1.1.4 =

- Enhancement - Clear events session on enqueue

= 1.1.5 =

- Enhancement - Add category hierarchy support for category recommendations
- Fix - Handle add to cart events via hooks over javascript

= 1.1.6 =

- Enhancement - Remove hotslot template and default theme settings
- Enhancement - Remove hooks dependent hotslots