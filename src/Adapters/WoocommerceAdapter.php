<?php

namespace PicupTechnologies\WooPicupShipping\Adapters;

use PicupTechnologies\WooPicupShipping\Interfaces\WoocommerceAdapterInterface;
use WC_Cart;
use WC_Order;

/**
 * Adapter to handle any WooCommerce specific functions
 *
 * @package PicupTechnologies\WooPicupShipping\Adapters
 */
final class WoocommerceAdapter implements WoocommerceAdapterInterface
{
    /**
     * Checks if woocommerce is installed
     *
     * @return bool
     */
    public function checkIfWoocommerceInstalled(): bool
    {
        return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), false);
    }

    /**
     * Returns the users cart items
     *
     * @return WC_Cart
     */
    public function getCart(): WC_Cart
    {
        return WC()->cart;
    }

    /**
     * Returns the users order
     *
     * @param int $id
     *
     * @return WC_Order
     */
    public function getOrder(int $id): WC_Order
    {
        return wc_get_order($id);
    }

    /**
     * Writes a notice message to the user
     *
     * @param $msg
     * @param $type
     */
    public function addNotice($msg, $type = null): void
    {
        \wc_add_notice($msg, $type);
    }

    public function addAdminNotice($msg): void
    {
        \WC_Admin_Notices::add_notice($msg);
    }

    /**
     * Adds a woo commerce select dropdown
     *
     * @param $options
     */
    public function addSelectDropdown($options): void
    {
        woocommerce_wp_select($options);
    }

    /**
     * Adds a woocommerce form field
     *
     * @param $key
     * @param $args
     * @param $default
     */
    public function woocommerceFormField($key, $args, $default): void
    {
        woocommerce_form_field($key, $args, $default);
    }
}
