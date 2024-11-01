<?php

namespace PicupTechnologies\WooPicupShipping\ShippingMethods;

use Exception;
use PicupTechnologies\WooPicupShipping\Adapters\WordpressAdapter;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use WC_Shipping_Method;

class PicupScheduled extends WC_Shipping_Method
{
    private $days = [
        '--',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

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

        $this->id = 'picup_scheduled_shipping_method';
        $this->instance_id = absint($instanceId);
        $this->method_title = __('Picup Scheduled');
        $this->method_description = __('Scheduled Shipping Method API for the Picup delivery service');
        $this->title = 'Picup Scheduled Shipping';
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
     * Creates form fields for admin page of Picup shipping plugin
     *
     * This must allow admin to add delivery shifts to the system in the format
     * of [day] - [start time] - [end time]
     */
    public function init_form_fields(): void
    {
        if (isset($_POST['data'])) {


            //print_r ($_POST);

            try {
                // grab post data. we will sanitize each used field in buildNewShift()
                $postData = $_POST['data'];

                // add new shift
                $this->updateShifts($postData);
                //$this->addShiftToExistingShifts($newShift);

                // remove shifts if requested
                //$this->handleShiftRemovalRequests($postData);
            } catch (Exception $e) {
                // could not add shift
            }
        }

        $shifts = $this->getShifts();


        $this->form_fields['header_add_shift'] = [
            'title'       => __('Add New Shift', 'woocommerce-picup'),
            'type'        => 'title',
            'default'     => '',
            /* translators: %s: URL for link. */
            'description' => sprintf(__('Add a new delivery shift to the system', 'woocommerce-picup')),
        ];

        $this->form_fields['add_shift_day'] = [
            'title'       => __('Day', 'woocommerce-picup'),
            'type'        => 'select',
            'default'     => 0,
            'description' => __('Set the shift day', 'woocommerce-picup'),
            'options'     => $this->days,
        ];

        $this->form_fields['add_shift_start_time'] = [
            'title'       => __('Start Time', 'woocommerce-picup'),
            'type'        => 'time',
            'default'     => 0,
            'description' => __('Set the shift start time', 'woocommerce-picup'),
        ];

        $this->form_fields['add_shift_end_time'] = [
            'title'       => __('End Time', 'woocommerce-picup'),
            'type'        => 'time',
            'default'     => 0,
            'description' => __('Set the shift end time', 'woocommerce-picup'),
        ];

        $this->form_fields['header_rate'] = [
            'title'       => __('Delivery Rate', 'woocommerce-picup'),
            'type'        => 'title',
            'default'     => '',
            /* translators: %s: URL for link. */
            'description' => sprintf(__('Picup Delivery Settings', 'woocommerce-picup')),
        ];

        $this->form_fields['picup_scheduled_rate'] = [
            'title'       => __('Rate', 'woocommerce-picup'),
            'type'        => 'text',
            'description' => __('Set the flat delivery rate', 'woocommerce-picup'),
            'default'     => $this->wordpressAdapter->getOption('picup_scheduled_rate'),
        ];
    }

    /**
     * Builds a shift to add the system in the admin
     *
     * @param $postData
     *
     * @return array|null
     */
    private function updateShifts($postData): ?array
    {


        //Loop through the fields to make the things happen

        $postShifts = [];
        foreach ($postData as $key => $value) {
            if (strpos($key, "shifts_") !== false) {
                $keyData = explode ("_", $key);
                $postShifts[$keyData[1]][$keyData[2]] = $value;
            }
        }


        $shifts = [];

        foreach ($postShifts as $id => $shift) {
            $newShift = [];
            $newShift['day'] = sanitize_text_field($shift["weekDay"]);
            $newShift['description'] = sanitize_text_field($shift["description"]);
            $newShift['start_time'] = sanitize_text_field($shift["timeFrom"]);
            $newShift['end_time'] = sanitize_text_field($shift["timeTo"]);
            $newShift['price'] = sanitize_text_field($shift["price"]);
            $newShift['cut_off_time'] = sanitize_text_field($postData['cutOffTime']);

            $shifts[] = $newShift;
        }



        if (!empty($shifts)) {
            $this->wordpressAdapter->updateOption('picup_scheduled_shifts', serialize($shifts));
        }

        $this->wordpressAdapter->updateOption('picup_display_delivery_date', $postData['displayDeliveryDate']);

        return $shifts;
    }


    public function getNextDeliveryDate($shiftDay) {

        $newDate = strtotime("next ".$this->days[$shiftDay]);

        return date("d M Y", $newDate);
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
        $shifts = $this->getShifts();
        $canDisplayDeliveryDate = $this->getDisplayDeliveryDate();

        foreach ($shifts as $id => $shift) {
            $shiftDescription = $shift["description"];
            if ($canDisplayDeliveryDate) {
                $shiftDescription .= " (delivered by: ".$this->getNextDeliveryDate($shift["day"])." - {$shift["end_time"]} )";
            }

            $rate = [
                'id' => "picup_shift_".$id,
                'label' => $shiftDescription,//,
                'cost' => $shift["price"],
                'calc_tax' => 'per_order',
                'taxes' => '',           // blank means woo calculates it for you. false means no tax.
            ];

            if ($shift["price"] > 0 && $this->getNextDeliveryDate($shift["day"]) !== date("d M Y", strtotime("tomorrow")) ) {
                $this->add_rate($rate);
            }
        }
    }

    //


    public function getShifts() {
        //Handle legacy plugins
        if (is_string($this->wordpressAdapter->getOption('picup_scheduled_shifts'))) {
            $shifts = unserialize($this->wordpressAdapter->getOption('picup_scheduled_shifts'));
        } else {
            $shifts =$this->wordpressAdapter->getOption('picup_scheduled_shifts');
        }
        return $shifts;
    }


    public function getDisplayDeliveryDate() {

        $displayDeliveryDate =$this->wordpressAdapter->getOption('picup_display_delivery_date');


        return $displayDeliveryDate;
    }

    public function generate_settings_html($form_fields = array(), $echo = true)
    {
        $shifts = $this->getShifts();
        $displayDeliveryDate = $this->getDisplayDeliveryDate();


       $template = realpath(__DIR__."/../../templates/shift-management.php");
       ob_start();

       include $template;
       return ob_get_clean();
    }
}
