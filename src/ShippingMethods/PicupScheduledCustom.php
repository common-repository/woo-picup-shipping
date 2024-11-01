<?php

namespace PicupTechnologies\WooPicupShipping\ShippingMethods;

use PicupTechnologies\WooPicupShipping\Adapters\WordpressAdapter;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use WC_Shipping_Method;

/**
 * Custom PicupScheduled shipping method
 *
 * This is a PicupScheduled method that takes any date the user provides and
 * creates a bucket with that date.
 *
 * This is on opposition to the standard PicupScheduled which requires that
 * the admin sets available delivery days in the admin.
 */
class PicupScheduledCustom extends WC_Shipping_Method
{
    /**
     * @var WordpressAdapter
     */
    private $wordpressAdapter;

    /**
     * PicupScheduled constructor.
     *
     * @param int $instanceId
     */
    public function __construct($instanceId = 0)
    {
        parent::__construct($instanceId);

        $this->id = 'picup_scheduled_custom_shipping_method';
        $this->instance_id = absint($instanceId);
        $this->method_title = __('Picup Scheduled Custom');
        $this->method_description = __('Custom Scheduled Shipping Method API for the Picup delivery service');
        $this->title = 'Picup Custom Scheduled Shipping';
        $this->supports = [
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        ];
        $this->tax_status = 'taxable';

        $this->enabled = $this->settings['enabled'] ?? 'yes';

        //woocommerce_after_checkout_validation - validate the shift was selected

        $this->wordpressAdapter = new WordpressAdapter();
        $this->picupApiOptions = PicupApiOptions::buildFromWordpress($this->wordpressAdapter->getOption('picup-api-options'));
        $this->init_form_fields();
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
     * Allows the user to set the PicupScheduledCustom shipping rate
     */
    public function init_form_fields(): void
    {
        if (isset($_POST['data'])) {
            $postData = $_POST['data'];

            if (isset($postData['woocommerce_picup_scheduled_custom_shipping_method_picup_scheduled_custom_rate']) && !empty($postData['woocommerce_picup_scheduled_custom_shipping_method_picup_scheduled_custom_rate'])) {
                $this->wordpressAdapter->updateOption('picup_scheduled_custom_rate', sanitize_text_field($postData['woocommerce_picup_scheduled_custom_shipping_method_picup_scheduled_custom_rate']));
            }
        }

        $this->form_fields['header_rate'] = [
            'title'       => __('Delivery Rate', 'woocommerce-picup'),
            'type'        => 'title',
            'default'     => '',
            /* translators: %s: URL for link. */
            'description' => sprintf(__('Picup Delivery Settings', 'woocommerce-picup')),
        ];

        $this->form_fields['picup_scheduled_custom_rate'] = [
            'title'       => __('Rate', 'woocommerce-picup'),
            'type'        => 'text',
            'description' => __('Set the delivery rate', 'woocommerce-picup'),
            'default'     => $this->wordpressAdapter->getOption('picup_scheduled_custom_rate'),
        ];
    }

    /**
     * Maybe adds Picup shipping rates to the frontend
     *
     * @param array $args
     * @param bool  $package
     */
    public function calculate_shipping($args = [], $package = false): void
    {
        if ($this->freeShippingValid()) return;
        // fetch the flat rate set by admin
        $rate = $this->wordpressAdapter->getOption('picup_scheduled_custom_rate');

        $rate = [
            'id'       => $this->id,
            'label'    => $this->title,
            'cost'     => $rate,
            'calc_tax' => 'per_order',
            'taxes'    => '',           // blank means woo calculates it for you. false means no tax.
        ];

        $this->add_rate($rate);
    }
}
