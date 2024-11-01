<?php
/*
  Plugin Name: Picup Shipping for WooCommerce
  Plugin URI: https://www.picup.co.za/
  Description: A shipping plugin for the Picup shipping service
  Version: 3.0
  Author: PicupTechnologies
  Author URI: https://www.picup.co.za
  WC requires at least: 3.0.0
  WC tested up to: 4.1
 */

namespace PicupTechnologies\WooPicupShipping;

require 'vendor/autoload.php';

// Start plugin
new WooPicupShipping();
