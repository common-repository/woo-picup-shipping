<?php

namespace PicupTechnologies\WooPicupShipping\ShippingMethods;

use Exception;
use PicupTechnologies\WooPicupShipping\Adapters\WordpressAdapter;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use WC_Shipping_Method;

class PicupFreeShipping extends WC_Shipping_Method
{

    /**
     * @var WordpressAdapter
     */
    private $wordpressAdapter;

    /**
     * PicupGenericOrder constructor.
     *
     * @param int $instanceId
     */
    public function __construct($instanceId = 0)
    {
        parent::__construct($instanceId);
        $this->wordpressAdapter = new WordpressAdapter();
        $this->id = 'picup_free_shipping_method';
        $this->instance_id = absint($instanceId);
        $this->method_title = __('Picup Free Shipping');
        $this->method_description = __('Free Shipping Method API for the Picup delivery service');
        $this->title = 'Picup Free Shipping';
        $this->supports = [
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        ];
        $this->tax_status = 'taxable';

        $this->enabled = true;

        //woocommerce_after_checkout_validation - validate the shift was selected
        $this->wordpressAdapter = new WordpressAdapter();
        $this->picupApiOptions = PicupApiOptions::buildFromWordpress($this->wordpressAdapter->getOption('picup-api-options'));

    }

    /**
     * Checks to see if free shipping is valid
     * @return bool
     */
    public function freeShippingValid() {
        $cartTotal =  WC()->cart->get_subtotal();
        if ($this->picupApiOptions->getFreeShippingEnabled() && (float) $cartTotal >= (float) $this->picupApiOptions->getFreeShippingPriceThreshold()) {
            return true;
        }
        return false;
    }

    /**
     * Picup shipping rates to the frontend
     *
     * @param array $args
     * @param bool  $package
     */
    public function calculate_shipping($args = [], $package = false): void
    {
           if ($this->freeShippingValid()) {

               $rate = [
                   'id' => "picup_option_" . $id,
                   'label' => "Free Shipping for Orders above ".get_woocommerce_currency_symbol()." ".number_format($this->picupApiOptions->getFreeShippingPriceThreshold(),2),
                   'cost' => 0.00,
                   'calc_tax' => 'per_order',
                   'taxes' => ''
               ];

               $this->add_rate($rate);
           }
    }

}
