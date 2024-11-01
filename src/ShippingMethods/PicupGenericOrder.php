<?php

namespace PicupTechnologies\WooPicupShipping\ShippingMethods;

use Exception;
use PicupTechnologies\WooPicupShipping\Adapters\WordpressAdapter;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use WC_Shipping_Method;

class PicupGenericOrder extends WC_Shipping_Method
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

        $this->id = 'picup_generic_shipping_method';
        $this->instance_id = absint($instanceId);
        $this->method_title = __('Picup Generic');
        $this->method_description = __('Generic Shipping Method API for the Picup delivery service');
        $this->title = 'Picup Generic Order';
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
     * Creates form fields for admin page of Picup shipping plugin
     *
     * This must allow admin to add delivery shifts to the system in the format
     * of [day] - [start time] - [end time]
     */
    public function init_form_fields(): void
    {
        if (isset($_POST['data'])) {

            try {
                // grab post data. we will sanitize each used field in buildNewShift()
                $postData = $_POST['data'];

                // add new shift
                $this->updateOptions($postData);
                //$this->addShiftToExistingShifts($newShift);

                // remove shifts if requested
                //$this->handleShiftRemovalRequests($postData);
            } catch (Exception $e) {
                // could not add shift
            }
        }
    }

    /**
     * Builds a shift to add the system in the admin
     *
     * @param $postData
     *
     * @return array|null
     */
    private function updateOptions($postData): ?array
    {
        //Loop through the fields to make the things happen

        $postOptions = [];
        foreach ($postData as $key => $value) {
            if (strpos($key, "options_") !== false) {
                $keyData = explode ("_", $key);
                $postOptions[$keyData[1]][$keyData[2]] = $value;
            }
        }



        foreach ($postOptions as $id => $option) {
            $newOption = [];

            $newOption['description'] = sanitize_text_field($option["description"]);
            $newOption['price'] = sanitize_text_field($option["price"]);


            $options[] = $newOption;
        }

        if (!empty($options)) {
            $this->wordpressAdapter->updateOption('picup_generic_options', serialize($options));
        }


        return $options;
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
     * Maybe adds Picup shipping rates to the frontend
     *
     * @param array $args
     * @param bool  $package
     */
    public function calculate_shipping($args = [], $package = false): void
    {
        if ($this->freeShippingValid()) return;

        $options = unserialize($this->wordpressAdapter->getOption('picup_generic_options'));


        foreach ($options as $id => $option) {
            $rate = [
                'id' => "picup_option_".$id,
                'label' => $option["description"],
                'cost' => $option["price"],
                'calc_tax' => 'per_order',
                'taxes' => ''           // blank means woo calculates it for you. false means no tax.
            ];

            $this->add_rate($rate);
        }
    }

    //




    public function generate_settings_html($form_fields = array(), $echo = true)
    {
        $options = unserialize($this->wordpressAdapter->getOption('picup_generic_options'));


       $template = realpath(__DIR__."/../../templates/generic-management.php");
       ob_start();

       include $template;
       return ob_get_clean();
    }
}
