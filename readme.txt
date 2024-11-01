=== Picup Shipping for WooCommerce ===
Contributors: picupshipping
Tags: shipping
Requires at least: 5.1
Tested up to: 5.7.2
Requires PHP: 7.2
Stable tag: 3.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds Picup as a Shipping Method to WooCommerce

== Description ==

This plugin will allow you to use Picup as a delivery service for your physical goods on your WooCommerce store. Picup is a last-mile delivery service that focuses on 100% visibility and accountability of each individual parcel.

== System Requirements ==
- PHP 7.1 is required by 7.3 is recommended
- PHP 5.6 can be supported on request but it is highly recommended you upgrade to 7.3. 5.6 reached end-of-life on December 31, 2018. Wordpress + Woocommerce both support PHP7 so upgrading should not break your shop.

== Before Installation ==
Register with Picup.co.za to add your business information and obtain an API key

== Installation ==
1. Go to: WordPress Admin > Plugins and search for "Picup Shipping"
2. Install Now and Activate the extension.
3. Configure it in the Picup API menu

== Setup ==
* Add your Picup API key
* Configure your WooCommerce Shipping Zones and set the desired shipping methods

== OnDemand Shipping Method ==
For immediate delivery you can provide the OnDemand service. This fetches a live quote from Picup at the point of checkout.

== Scheduled Shipping Method ==
Should you want to specify specific days and times to offer delivery you may set up delivery shifts along with the fixes rates in the shipping method settings.

== ScheduledCustom Shipping Method ==
This is similar to PicupScheduled except here we let you provide any date you wish and we will create a bucket from 08:00 to 17:00 that day. Use this if you have complex shipping conditions which have been set elsewhere.

== Support ==
Should you encounter any issues/bugs then please contact support at <a href="mailto:devops@picup.co.za">devops@picup.co.za</a>

== Frequently Asked Questions ==
= I'm receiving the error "The address you provided is too vague" =
This means that Google returned multiple addresses for the one provided. Please try and be more specific with your address.

= My orders are still not going through =
Please ensure that you have either set the correct parcel size for your products or you have specified product dimensions so we can try calculate the smallest parcel required.

== Changelog ==
= Version 2.2.12 =
* Added an empty selection to the delivery shift dropdown. Fixes bug where it was saving the shift even for OnDemand.

= Version 2.2.11 =
* Fixed bug where the DeliveryShiftCollection was not retrieving the correct shift for admin order status display

= Version 2.2.10 =
* Fixed bug where test keys were being used as live keys and vice-versa

= Version 2.2.9 =
* Add timestamp to the third party delivery rates

= Version 2.2.8 =
* Fixed bug where scheduled shifts were not being displayed

= Version 2.2.7 =
* Add support for instances where third party responses are enabled but none are returned for an area

= Version 2.2.6 =
* Updated Picup API to v1.3.3 which added support for instances where an area is not serviced by Picup but it is serviced by third party couriers
* Fixed bug where apartment number wasn't being included in delivery address

= Version 2.2.5 =
* Fixed bug with Scheduled delivery when going from cart -> checkout

= Version 2.2.4 =
* Fixed bug where scheduled delivery shifts were not being removed

= Version 2.2.3 =
* Fixed bug in ApiBuilder
* Renamed ThirdParty Courier shipping labels to include delivery date

= Version 2.2.2 =
* Removed test dates from ScheduledCustom

= Version 2.2.1 =
* Added setting to let shop owner set format of ScheduledCustom date

= Version 2.2.0 =
* Added support for third party couriers
