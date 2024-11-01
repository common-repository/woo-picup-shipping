<?php

namespace PicupTechnologies\WooPicupShipping\Interfaces;

use WC_Cart;
use WC_Order;

/**
 * Interface WoocommerceAdapter
 *
 * @package PicupTechnologies\WooPicupShipping\Interfaces
 */
interface WoocommerceAdapterInterface
{
    /**
     * Checks if woocommerce is installed
     *
     * @return bool
     */
    public function checkIfWoocommerceInstalled(): bool;

    /**
     * Returns the users cart items
     *
     * @return WC_Cart
     */
    public function getCart(): WC_Cart;

    /**
     * Returns a users order details
     *
     * @param int $id
     *
     * @return WC_Order
     */
    public function getOrder(int $id): WC_Order;

    /**
     * Writes a notice message to the user
     *
     * @param $msg
     * @param $type
     */
    public function addNotice($msg, $type = null): void;

    /**
     * Adds a woo commerce select dropdown
     *
     * @param $options
     */
    public function addSelectDropdown($options): void;

    /**
     * Adds a woocommerce form field
     *
     * @param $key
     * @param $args
     * @param $default
     */
    public function woocommerceFormField($key, $args, $default): void;
}
