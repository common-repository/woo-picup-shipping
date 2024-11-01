<?php

namespace PicupTechnologies\WooPicupShipping;

use DateTime;
use Exception;
use PicupTechnologies\PicupPHPApi\Exceptions\PicupApiException;
use PicupTechnologies\PicupPHPApi\Exceptions\PicupApiKeyInvalid;
use PicupTechnologies\PicupPHPApi\Exceptions\PicupRequestFailed;
use PicupTechnologies\PicupPHPApi\PicupApi;
use PicupTechnologies\PicupPHPApi\Requests\OrderStatusRequest;
use PicupTechnologies\PicupPHPApi\Requests\StandardBusinessRequest;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryIntegrationDetailsResponse;
use PicupTechnologies\WooPicupShipping\Adapters\WoocommerceAdapter;
use PicupTechnologies\WooPicupShipping\Adapters\WordpressAdapter;
use PicupTechnologies\WooPicupShipping\Builders\DeliveryBucketRequestBuilder;
use PicupTechnologies\WooPicupShipping\Builders\DeliveryOrderRequestBuilder;
use PicupTechnologies\WooPicupShipping\Builders\IntegrationDetailsResponseBuilder;
use PicupTechnologies\WooPicupShipping\Builders\PicupApiBuilder;
use PicupTechnologies\WooPicupShipping\Builders\StageOrderRequestBuilder;
use PicupTechnologies\WooPicupShipping\Builders\ThirdPartyCollectionRequestBuilder;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use PicupTechnologies\WooPicupShipping\DeliveryShifts\DeliveryShiftCollection;
use PicupTechnologies\WooPicupShipping\DeliveryShifts\DeliveryShiftPresenter;
use PicupTechnologies\WooPicupShipping\DeliveryShifts\DeliveryShiftSorter;
use PicupTechnologies\WooPicupShipping\Formatters\IntegrationDetailsFormatter;
use PicupTechnologies\WooPicupShipping\Interfaces\WoocommerceAdapterInterface;
use PicupTechnologies\WooPicupShipping\Interfaces\WordpressAdapterInterface;
use PicupTechnologies\WooPicupShipping\Notices\PicupApiKeyMissingNotice;
use PicupTechnologies\WooPicupShipping\Notices\PicupApiTestingMode;
use PicupTechnologies\WooPicupShipping\Notices\PicupErrorNotice;
use PicupTechnologies\WooPicupShipping\ShippingMethods\PicupFreeShipping;
use PicupTechnologies\WooPicupShipping\ShippingMethods\PicupGenericOrder;
use PicupTechnologies\WooPicupShipping\ShippingMethods\PicupOnDemand;
use PicupTechnologies\WooPicupShipping\ShippingMethods\PicupScheduled;
use PicupTechnologies\WooPicupShipping\ShippingMethods\PicupScheduledCustom;
use WC_Order;
use WC_Product_Simple;
use WC_Shipping_Method;
use WC_Shipping_Zones;
use WP_Post;

final class WooPicupShipping
{
    public $zones;

    /**
     * Holds the PicupApi instance
     *
     * @var PicupApi
     */
    private $picupApi;

    /**
     * Holds all the options used by the plugin and stored in the database
     *
     * @var PicupApiOptions
     */
    private $picupApiOptions;

    /**
     * When we initialize the API we perform an integration details request to check if
     * the API key is valid. This holds that response.
     *
     * @var DeliveryIntegrationDetailsResponse
     */
    private $picupIntegrationDetailsResponse;

    /**
     * @var PicupOnDemand
     */
    private $picupOnDemand;

    /**
     * @var PicupScheduled
     */
    private $picupScheduled;

    /**
     * @var PicupScheduledCustom
     */
    private $picupScheduledCustom;

    /**
     * @var PicupGenericOrder
     */
    private $picupGenericOrder;


    /**
     * @var WordpressAdapterInterface
     */
    private $wordpressAdapter;

    /**
     * @var WoocommerceAdapterInterface
     */
    private $woocommerceAdapter;

    /**
     * WooPicupShipping constructor.
     */
    public function __construct()
    {
        $this->wordpressAdapter = new WordpressAdapter();
        $this->woocommerceAdapter = new WoocommerceAdapter();

        if (!$this->woocommerceAdapter->checkIfWoocommerceInstalled()) {
            $this->woocommerceAdapter->addNotice('This plugin requires WooCommerce to be installed and activated');

            return;
        }

        $this->initApi();
        $this->addActionsAndHooks();
    }

    /**
     * Responsible for building the API and testing if the key is valid
     */
    private function initApi(): void
    {
        $this->picupApiOptions = PicupApiOptions::buildFromWordpress($this->wordpressAdapter->getOption('picup-api-options'));

        // Display message if no API key has been provided yet
        if (!$this->picupApiOptions->getApiKey() && $this->picupApiOptions->isLiveMode()) {
            new PicupApiKeyMissingNotice();
            return;
        }

        // Display message if no API key has been provided yet
        if (!$this->picupApiOptions->getTestApiKey()) {
            new PicupApiKeyMissingNotice('The Picup Plugin requires a valid api key to work <br> No account? Head over to <a mailto:"picup.co.za">picup.co.za</a> to register or chat to a sales agent.');
            return;
        }

        // Display message if picup api is in testing mode
        if (!$this->picupApiOptions->isLiveMode()) {
            new PicupApiTestingMode();
        }

        $this->picupApi = PicupApiBuilder::make($this->picupApiOptions);

        try {
            if (strpos($_SERVER["REQUEST_URI"], "admin.php") !== false || strpos($_SERVER["REQUEST_URI"], "wp-admin") !== false)
            {

                $fileName= __DIR__."/settings".md5($this->picupApi->getApiKey()).".json";
                if (!file_exists($fileName)) {
                    // request integration details with current api key
                    $request = new StandardBusinessRequest($this->picupApi->getApiKey());
                    $this->picupIntegrationDetailsResponse = $this->picupApi->sendIntegrationDetailsRequest($request);
                    file_put_contents($fileName, serialize($this->picupIntegrationDetailsResponse));
                } else {
                    $this->picupIntegrationDetailsResponse =  unserialize(file_get_contents($fileName));
                }

                // store in database
                $this->wordpressAdapter->updateOption('picup_integration', IntegrationDetailsFormatter::format($this->picupIntegrationDetailsResponse));
            }
        } catch (PicupApiException $e) {
            new PicupErrorNotice($e->getMessage());
        }
    }

    public function addActionsAndHooks(): void
    {
        add_action('woocommerce_init', [$this, 'woocommerce_init']);

        // configure the shipping methods
        add_action('woocommerce_shipping_init', [$this, 'shipping_method_init']);

        // add them to the list of shipping_methods
        add_filter('woocommerce_shipping_methods', [$this, 'add_shipping_method']);


        if ($this->picupApiOptions->getDeliveryCreationSetting() === 1) {
            // performs the actual placement of the delivery with picup after payment is complete
            add_action('woocommerce_order_status_completed', [$this, 'shipping_payment_complete']);
        } else {
            add_action('woocommerce_order_status_processing', [$this, 'shipping_payment_complete']);
        }
        // validate that the user has selected a shift
        add_action('woocommerce_checkout_process', [$this, 'delivery_shift_field_process']);

        // set a cookie ith their delivery shift choice
        add_action('woocommerce_checkout_update_order_meta', [$this, 'delivery_shift_field_update_order_meta']);

        // show the selected shift in the Order Review page
        add_action('woocommerce_review_order_before_shipping', [$this, 'delivery_shift_field_display_admin_order_meta']);

        add_action('wp_enqueue_scripts', static function () {
            wp_register_style('picup_api', plugins_url('../styles/default.css', __FILE__));
            wp_enqueue_style('picup_api');
        });

        add_filter('woocommerce_package_rates', [$this, 'change_shipping_method_name_based_on_shipping_class'], 50, 2);
        // Add delivery shift radio to the checkout
        add_action('woocommerce_after_shipping_rate', [$this, 'add_delivery_shift_radios_to_checkout'], 10, 2);

        add_action('woocommerce_after_shipping_rate', [$this, 'add_generic_options_radios_to_checkout'], 10, 2);


        // add template overriding
        add_filter('woocommerce_locate_template', [$this, 'locate_custom_templates'], 10, 3);

        // add shipping estimator extra fields updater
        add_action('woocommerce_calculated_shipping', [$this, 'save_extra_shipping_estimator_fields']);

        if (is_admin()) {
            // add the custom picup api menu
            add_action('admin_menu', [$this, 'picup_api_settings_create_menu']);

            // register the picup api settings
            add_action('admin_init', [$this, 'register_picup_api_settings']);

            // add picup order status widget to Order Details page
            add_action('woocommerce_order_details_after_customer_details', [$this, 'picup_order_status_output'], 10, 1);

            // add custom parcel size options to Edit Product -> Shipping
            add_action('add_meta_boxes', [$this, 'add_custom_meta_boxes']);

            add_action('woocommerce_product_options_shipping', [$this, 'additional_product_shipping_options']);

            // add custom parcel options to variable product options
            add_action('woocommerce_variation_options_dimensions', [$this, 'variable_picup_parcel_size'], 10, 3);

            // processes _variable_picup_parcel_size attribute submission 
            add_action('woocommerce_save_product_variation', [$this, 'variable_picup_parcel_size_save'], 10, 2);

            // hook to update the parcel metadata for a product
            add_action('woocommerce_process_product_meta', [$this, 'save_parcel_size_meta']);

            // show shift in order details after billing address
            add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'delivery_shift_field_display_admin_order_meta'], 10, 1);
        }
    }

    private function getShifts() {
        //Handle legacy plugins
        if (is_string($this->wordpressAdapter->getOption('picup_scheduled_shifts'))) {
            $shifts = unserialize($this->wordpressAdapter->getOption('picup_scheduled_shifts'));
        } else {
            $shifts =$this->wordpressAdapter->getOption('picup_scheduled_shifts');
        }
        return $shifts;
    }

    public function variable_picup_parcel_size($loop, $variation_data, $variation)
    {
        $picupIntegration = $this->wordpressAdapter->getOption('picup_integration');

        if (!$picupIntegration) {
            return;
        }

        echo '<div class="options_group form-row form-row-full">';

        woocommerce_wp_select(
            [
                'id' => '_picup_parcel_size[' . $variation->ID . ']',
                'label' => __('Picup Parcel Size', 'woocommerce-picup'),
                'options' => $picupIntegration['parcel_sizes'],
                'value' => get_post_meta($variation->ID, '_variable_picup_parcel_size', true),
                'placeholder' => '',
            ]
        );

        echo '</div>';
    }

    function variable_picup_parcel_size_save($post_id)
    {
        $parcelSize = $_POST['_picup_parcel_size'][$post_id];
        update_post_meta($post_id, '_variable_picup_parcel_size', esc_attr($parcelSize));
    }

    public function woocommerce_init(): void
    {
        $this->zones = WC_Shipping_Zones::get_zones();
    }

    /**
     * Settings page for picup config
     */
    public function picup_api_settings_create_menu(): void
    {
        //create new top-level menu
        $this->wordpressAdapter->addMenuPage(
            'Picup API',
            'Picup API',
            'administrator',
            __FILE__,
            [$this, 'picup_api_settings_page']
        );
    }

    public function register_picup_api_settings(): void
    {
        $this->wordpressAdapter->registerSetting('picup-api-options-group', 'picup-api-options');
    }

    /**
     * Responsible for displaying the Picup API Settings page
     * and storing them in the database
     */
    public function picup_api_settings_page(): void
    {
        if (isset($_POST) && !empty($_POST)) {
            $apiOptions = $_POST['picup-api-options'];



            // sanitizing all options
            $options = [
                'api_key' => sanitize_text_field($apiOptions['api_key']),
                'test_api_key' => sanitize_text_field($apiOptions['test_api_key']),
                'live_mode' => sanitize_text_field($apiOptions['live_mode']),
                'special_instructions' => sanitize_text_field($apiOptions['special_instructions']),
                'name' => sanitize_text_field($apiOptions['name']),
                'email' => sanitize_email($apiOptions['email']),
                'telephone' => sanitize_text_field($apiOptions['telephone']),
                'cellphone' => sanitize_text_field($apiOptions['cellphone']),
                'scheduled_display_setting' => sanitize_text_field($apiOptions['scheduled_display_setting']),
                'delivery_creation_setting' => sanitize_text_field($apiOptions['delivery_creation_setting']),
                'ondemand_prep_time' => sanitize_text_field($apiOptions['ondemand_prep_time']),
                'is_consolidate_boxes' => sanitize_text_field($apiOptions['is_consolidate_boxes']),
                'consolidated_box_size' => sanitize_text_field($apiOptions['consolidated_box_size']),
                'consolidated_items_per_box' => sanitize_text_field($apiOptions['consolidated_items_per_box']),
                'scheduled_custom_date_format' => sanitize_text_field($apiOptions['scheduled_custom_date_format']),
                'third_party_couriers' => sanitize_text_field($apiOptions['third_party_couriers']),
                'contract_drivers'=> sanitize_text_field($apiOptions['contract_drivers']),
                'free_shipping_enabled' => sanitize_text_field($apiOptions['free_shipping_enabled']),
                'free_shipping_price_threshold' => sanitize_text_field($apiOptions['free_shipping_price_threshold']),
                'outside_south_africa_enabled' => sanitize_text_field($apiOptions['outside_south_africa_enabled'])
            ];



            
            // add warehouse zones.
            foreach ($apiOptions['warehouse_zones'] as $zoneKey => $zoneWarehouse) {
                $options['warehouse_zones'][sanitize_key($zoneKey)] = sanitize_text_field($zoneWarehouse);
            }

            $this->wordpressAdapter->updateOption('picup-api-options', $options);

            // okay now we have to refresh the page else for some reason
            // wordpress doesnt do it itself.

            $this->wordpressAdapter->redirect($_SERVER['HTTP_REFERER']);
        }

        // send the integration details and api options to the view
        $integrationDetails = $this->picupIntegrationDetailsResponse;
        $picupApiOptions = $this->picupApiOptions;
        $zones = $this->zones;

        include_once(__DIR__ . '/../templates/picup-api-settings.php');
    }

    /**
     * Handle processing of checkout.
     *
     * This ensures the user has selected a shift if they selected PicupScheduled
     */
    public function delivery_shift_field_process(): void
    {
        if (!isset($_POST['shipping_method'][0])) {
            return;
        }

        // Ignore delivery shift check if only virtual products are in the cart
        $virtualOnly = true;
        $cart = $this->woocommerceAdapter->getCart()->get_cart();

        foreach ($cart as $key => $cartItem) {
            /** @var WC_Product_Simple $cartProduct */
            $cartProduct = $cartItem['data'];

            // Check if there are non-virtual products
            if (!$cartProduct->get_virtual()) {
                $virtualOnly = false;
            }
        }

        if ($virtualOnly) {
            return;
        }

        // We must only check delivery shift presence when we have selected Scheduled or ScheduledCustom
        $shippingMethod = strtolower(sanitize_text_field($_POST['shipping_method'][0]));

        if ($shippingMethod === 'picup_scheduled_shipping_method') {
            // Check if set, if its not set add an error.
            if (!isset($_POST['picup_scheduled_shift'])) {
                $this->woocommerceAdapter->addNotice(__('Please select a delivery shift'), 'error');

                return;
            }

            $_SESSION['picup_scheduled_selected_shift'] = sanitize_text_field($_POST['picup_scheduled_shift']);
        }

        if ($shippingMethod === 'picup_scheduled_custom_shipping_method') {
            if (!isset($_POST['delivery_date']) || empty($_POST['delivery_date'])) {
                $this->woocommerceAdapter->addNotice(__('Please select a delivery date from the calendar'), 'error');

                return;
            }

            $_SESSION['picup_scheduled_custom_delivery_date'] = sanitize_text_field($_POST['delivery_date']);
        }
    }

    /**
     * Adds the delivery shift the user selected to the order metadata
     *
     * @param $orderId
     */
    public function delivery_shift_field_update_order_meta($orderId): void
    {
        if (isset($_SESSION['third_party']) && $_SESSION['third_party']) {
            $method = strtolower(sanitize_text_field($_POST['shipping_method'][0]));
            $responses = $_SESSION['third_party_responses'];

            $this->wordpressAdapter->updatePostMeta($orderId, 'third_party', true);
            $this->wordpressAdapter->updatePostMeta($orderId, 'third_party_method', $method);
            $this->wordpressAdapter->updatePostMeta($orderId, 'third_party_collection', $responses[$method]);
        }



        //Get the correct shift id
        if (isset($_POST['shipping_method'])) {


            foreach ($_POST['shipping_method'] as $id => $shipping) {
                if (strpos($shipping, "picup_shift") !== false) {
                    $shift = explode ("_", $shipping);
                    $shiftId = $shift[count($shift)-1];

                    $this->wordpressAdapter->updatePostMeta($orderId, 'picup_scheduled_selected_shift', $shiftId);
                }
            }

            return;
        }




        return;
    }

    private function delivery_shift_fetch_details($shiftId)
    {
        $shifts = $this->getShifts();

        return $shifts[$shiftId];
    }

    /**
     * Displays the shift the user selected in the admin order status page
     *
     * @throws Exception
     */
    public function delivery_shift_field_display_admin_order_meta(): void
    {
        global $post;

        $shiftId = $this->wordpressAdapter->getPostMeta($post->ID, 'picup_scheduled_selected_shift');

        if ($shiftId) {
            echo $this->formatPicupScheduledShift($shiftId);
            return;
        }

        $deliveryDate = $this->wordpressAdapter->getPostMeta($post->ID, 'delivery_date');
        if ($deliveryDate) {
            echo $this->formatPicupScheduledCustomDeliveryDate($deliveryDate);
            return;
        }
    }

    /**
     * Formats a PicupScheduled shift for output in the Order Details admin page
     *
     * @param $shiftId
     *
     * @return string|void
     * @throws Exception
     */
    private function formatPicupScheduledShift($shiftId): ?string
    {
        $shifts = $this->getShifts();

        if (empty($shifts)) {
            return null;
        }

        // build shift collection
        $currentDateTime = new DateTime();
        $deliveryShiftCollection = DeliveryShiftCollection::buildFromWordpressData($shifts, $currentDateTime);

        // return the selected shift
        $actualShiftUserSelected = $deliveryShiftCollection->getShift($shiftId);

        $shiftText = $actualShiftUserSelected->getShiftStartDate()->format(DATE_W3C);

        return '<p><strong>' . __('PicupScheduled Shift') . ':</strong> ' . $shiftText . '</p>';
    }

    /**
     * Formats a PicupScheduledCustom delivery date for output in the Order Details admin page
     *
     * @param $deliveryDateString
     *
     * @return string
     */
    private function formatPicupScheduledCustomDeliveryDate($deliveryDateString): string
    {
        $deliveryDate = DateTime::createFromFormat('F j, Y', $deliveryDateString);

        return '<p><strong>' . __('Delivery Date') . ':</strong> ' . $deliveryDate->format(DATE_W3C) . '</p>';
    }

    /**
     * Adds the delivery shift radios to the checkout for the user
     * to select during cart + checkout
     *
     * @param WC_Shipping_Method $method
     * @param int                $index
     *
     * @throws Exception
     */
    public function add_delivery_shift_radios_to_checkout(
        $method,
        /** @noinspection PhpUnusedParameterInspection */
        $index
    ): void {
        // only add shift selector for scheduled picup
        if ($method->id !== 'picup_scheduled_shipping_method') {
            return;
        }

        if (!$this->checkIfMustDisplayDeliveryShiftDropdown()) {
            return;
        }

        $shifts = $this->getShifts();

        if (empty($shifts)) {
            return;
        }

        // build shift collection
        $currentDateTime = new DateTime();
        $deliveryShiftCollection = DeliveryShiftCollection::buildFromWordpressData($shifts, $currentDateTime);

        // sort shifts
        $deliveryShiftSorter = new DeliveryShiftSorter();
        $sortedShiftCollection = $deliveryShiftSorter->sortShifts($deliveryShiftCollection);

        // present shifts
        $deliveryShiftPresenter = new DeliveryShiftPresenter($sortedShiftCollection);
        $options = ['' => 'Select shift'];
        $options += $deliveryShiftPresenter->presentShifts();

        $args = [
            'type'    => 'select',
            'class'   => ['delivery_shifts'],
            'options' => $options,
        ];

        if (isset($_SESSION['picup_scheduled_selected_shift']) && !empty($_SESSION['picup_scheduled_selected_shift'])) {
            $selectedShift = sanitize_text_field($_SESSION['picup_scheduled_selected_shift']);
            $args['default'] = $selectedShift;
        }

        $this->woocommerceAdapter->woocommerceFormField('picup_scheduled_shift', $args, $selectedShift);
    }






    public function add_generic_options_radios_to_checkout(
        $method,
        /** @noinspection PhpUnusedParameterInspection */
        $index
    ): void {

        // only add shift selector for scheduled picup
        if ($method->id !== 'picup_generic_shipping_method') {
            return;
        }

        $options = unserialize($this->wordpressAdapter->getOption('picup_generic_options'));


        if (empty($options)) {
            return;
        }


        $args = [
            'type'    => 'select',
            'class'   => ['delivery_options'],
            'options' => $options,
        ];

        if (isset($_SESSION['picup_generic_selected_shift']) && !empty($_SESSION['picup_generic_selected_shift'])) {
            $selectedOption = sanitize_text_field($_SESSION['picup_generic_selected_shift']);
            $args['default'] = $selectedOption;
        }

        $this->woocommerceAdapter->woocommerceFormField('picup_generic_selected_shift', $args, $selectedOption);
    }

    /**
     * The admin has the option of setting where they would like the delivery
     * shift dropdown to appear.
     *
     * This ensures we are showing it where we should
     *
     * @return bool
     */
    private function checkIfMustDisplayDeliveryShiftDropdown(): bool
    {
        // Show both
        if ($this->picupApiOptions->getScheduledDisplaySetting() === 3) {
            return true;
        }

        // Show cart only
        if (is_cart() && $this->picupApiOptions->getScheduledDisplaySetting() === 1) {
            return true;
        }

        // Show checkout only
        if (is_checkout() && $this->picupApiOptions->getScheduledDisplaySetting() === 2) {
            return true;
        }

        // No-show!
        return false;
    }

    /**
     * Adds any custom meta boxes required for the Picup Shipping plugin
     */
    public function add_custom_meta_boxes(): void
    {
        $this->wordpressAdapter->addMetaBox(
            'picup-order-status-metabox',
            'Picup Order Status',
            [$this, 'picup_order_status_output',],
            'shop_order',
            'side',
            'high'
        );
    }

    /**
     * Renders Picup order status data for metabox and frontend
     *
     * @param $order
     *
     * @throws PicupApiException
     * @throws PicupApiKeyInvalid
     * @throws PicupRequestFailed
     */
    public function picup_order_status_output(WP_Post $order): void
    {
        $orderId = $order->ID;

        // We already have a request id attached to this order
        $picupId = $this->wordpressAdapter->getPostMeta($orderId, 'picup_request_id', true);
        echo "<style>display:none</style>";
        if (empty($picupId)) {
            echo 'Tracking is not yet available for this Picup';
            return;
        }
        // We need to obtain the picup status for this order
        $request = new OrderStatusRequest([$orderId]);


        $orderStatusResponse = $this->picupApi->sendOrderStatusRequest($picupId);

        if (!is_object($orderStatusResponse)) {
            echo '<style>#picup-order-status-metabox{display: none;}</style>';
            return;
        }

        if (is_object($orderStatusResponse) && empty($orderStatusResponse->getOrderStatuses())) {
            echo '<style>#picup-order-status-metabox{display: none;}</style>';
            return;
        }

        include_once(__DIR__ . '/../templates/picup-order-status.php');
    }

    /**
     * Initializes the shipping methods
     */
    public function shipping_method_init(): void
    {
        //$this->picupOnDemand = new PicupOnDemand();
        $this->picupScheduled = new PicupScheduled();
        $this->picupScheduledCustom = new PicupScheduledCustom();
        $this->picupGenericOrder = new PicupGenericOrder();
        $this->picupFreeShipping = new PicupFreeShipping();
    }

    /**
     * Checks to see if free shipping is valid
     * @return bool
     */
    public function freeShippingValid() {
        $cartTotal =  WC()->cart->get_subtotal();

        if ($this->picupApiOptions->getFreeShippingEnabled() && (float)$cartTotal >=(float)$this->picupApiOptions->getFreeShippingPriceThreshold()) {
            return true;
        }
        return false;
    }

    /**
     * Adds our available shipping methods
     *
     * @param $methods
     *
     * @return array
     */
    public function add_shipping_method($methods): array
    {

        $methods['picup_free_shipping_method'] = PicupFreeShipping::class;
        $methods['picup_generic_shipping_method'] = PicupGenericOrder::class;
        $methods['picup_scheduled_shipping_method'] = PicupScheduled::class;
        $methods['picup_scheduled_custom_shipping_method'] = PicupScheduledCustom::class;

        return $methods;
    }

    /**
     * Creates post meta for products for Picup parcel dimensions
     */
    public function additional_product_shipping_options(): void
    {
        $picupIntegration = $this->wordpressAdapter->getOption('picup_integration');

        if (!$picupIntegration) {
            return;
        }

        $selectOptions = [
            'id'          => '_picup_parcel_size',
            'label'       => __('Picup Parcel Size', 'woocommerce-picup'),
            'options'     => $picupIntegration['parcel_sizes'],
            'placeholder' => '',
        ];

        $this->woocommerceAdapter->addSelectDropdown($selectOptions);
    }

    /**
     * Updates product post meta for custom meta for Picup parcel
     *
     * @param $postId
     */
    public function save_parcel_size_meta($postId): void
    {
        if (!isset($_POST['_picup_parcel_size'])) {
            return;
        }

        $picupParcelSize = sanitize_text_field($_POST['_picup_parcel_size']);

        if (empty($picupParcelSize)) {
            return;
        }

        $this->wordpressAdapter->updatePostMeta($postId, '_picup_parcel_size', $picupParcelSize);
    }

    /**
     * Places Picup order once order has been marked as complete
     *
     * PicupOndemand - Create Order with Picup
     * PicupScheduled - Add Order to Picup Bucket
     *
     * @param $orderId
     *
     * @throws PicupApiException
     * @throws PicupApiKeyInvalid
     * @throws PicupRequestFailed
     * @throws Exception
     */
    public function shipping_payment_complete($orderId): void
    {
        /** @var WC_Order $order */
        $order = $this->woocommerceAdapter->getOrder($orderId);

        // Get the zone for the order shipment
        $package = [
            'destination' => [
                'country'  => $order->get_shipping_country(),
                'state'    => $order->get_shipping_state(),
                'postcode' => $order->get_shipping_postcode(),
            ],
        ];
        $zone = WC_Shipping_Zones::get_zone_matching_package($package);

        // First we handle third party couriers because those are handled
        // via the post metadata
        if ($order->get_meta('third_party')) {
            $this->createThirdPartyOnDemand($order);
            return;
        }


        // get order shipping method
        if ($order->has_shipping_method('picup_ondemand_shipping_method')) {
            $this->createPicupOnDemand($order, $this->picupApiOptions, $zone->get_id());

            return;
        }

        if ($order->has_shipping_method('picup_scheduled_shipping_method')) {

            $this->createPicupScheduled($order, $this->picupApiOptions, $zone->get_id());

            return;
        }

        if ($order->has_shipping_method('picup_scheduled_custom_shipping_method')) {
            $deliveryDate = DateTime::createFromFormat($this->picupApiOptions->getScheduledCustomDateFormat(), $order->get_meta('delivery_date'));
            $deliveryDate->setTime(8, 0);
            $this->createPicupScheduled($order, $this->picupApiOptions, $zone->get_id(), $deliveryDate);

            return;
        }

        if ($order->has_shipping_method('picup_generic_shipping_method') || $order->has_shipping_method('picup_free_shipping_method')) {
            $this->createPicupGeneric($order, $this->picupApiOptions, $zone->get_id());
            return;
        }
    }

    private function createPicupGeneric(WC_Order $order, PicupApiOptions $picupApiOptions, $zoneId): void
    {

        $integrationDetails = IntegrationDetailsResponseBuilder::make($this->wordpressAdapter->getOption('picup_integration'));
        $request = StageOrderRequestBuilder::make($order, $picupApiOptions, $integrationDetails, $zoneId);



        // send the courier collection to picup
        $response = $this->picupApi->sendStageOrder($request);

        // store request_id with order metadata
        $this->wordpressAdapter->addPostMeta($order->get_id(), 'picup_waybill_no', $response->validations[0]->waybill_number);
    }

    /**
     * Handles the actual creation of the PicupOnDemand
     *
     * This includes third party courier orders too
     *
     * @param WC_Order        $order
     * @param PicupApiOptions $picupApiOptions
     * @param                 $zoneId
     *
     * @throws PicupApiException
     * @throws PicupApiKeyInvalid
     * @throws PicupRequestFailed
     * @throws Exception           Should the DateTime creation fail
     */
    private function createPicupOnDemand(WC_Order $order, PicupApiOptions $picupApiOptions, $zoneId): void
    {
        $orderId = $order->get_id();

        $picupId = $this->wordpressAdapter->getPostMeta($orderId, 'picup_request_id');
        if (!empty($picupId)) {
            $this->woocommerceAdapter->addAdminNotice(sprintf('Picup has already been created for this order (ID #%s)', $picupId));
            return;
        }

        $isThirdParty = $order->get_meta('third_party');
        if ($isThirdParty) {
            $this->createThirdPartyOnDemand($order);
            return;
        }

        $integrationDetails = IntegrationDetailsResponseBuilder::make($this->wordpressAdapter->getOption('picup_integration'));
        $request = DeliveryOrderRequestBuilder::make($order, $picupApiOptions, $integrationDetails, $zoneId);
        $response = $this->picupApi->sendOrderRequest($request);

        $this->wordpressAdapter->addPostMeta($orderId, 'picup_request_id', $response->getId());
    }

    /**
     * Creates a third party courier collection picup
     *
     * @param WC_Order        $order
     *
     * @throws PicupApiException
     * @throws PicupApiKeyInvalid
     * @throws PicupRequestFailed
     * @throws Exception           Should the DateTime creation fail
     */
    private function createThirdPartyOnDemand(WC_Order $order): void
    {
        $picupId = $this->wordpressAdapter->getPostMeta($order->get_id(), 'picup_request_id');

        //????
        if (!empty($picupId)) {
            $this->woocommerceAdapter->addAdminNotice(sprintf('Picup has already been created for this order (ID #%s)', $picupId));
            return;
        }

        $collectionResponse = $order->get_meta('third_party_collection');
        $collectionJson = json_decode($collectionResponse);


        // build a third party collection request from the collection
        $request = ThirdPartyCollectionRequestBuilder::make($collectionJson);




        // send the courier collection to picup
        $response = $this->picupApi->sendThirdPartyCourierCollection($request);



        // store request_id with order metadata
        $this->wordpressAdapter->addPostMeta($order->get_id(), 'picup_request_id', $response->getId());
    }

    /**
     * Handle the actual creation of the PicupScheduled
     *
     * @param WC_Order        $order
     * @param PicupApiOptions $picupApiOptions
     * @param                 $zoneId
     *
     * @param DateTime|null   $deliveryDate
     *
     * @throws PicupApiException
     * @throws PicupApiKeyInvalid
     * @throws PicupRequestFailed
     * @throws Exception          Should the DateTime creation fail
     */
    private function createPicupScheduled(WC_Order $order, PicupApiOptions $picupApiOptions, $zoneId, DateTime $deliveryDate = null): void
    {
        $orderId = $order->get_id();

        $picupId = $this->wordpressAdapter->getPostMeta($orderId, 'picup_bucket_id', true);


        if (!empty($picupId)) {
            $this->woocommerceAdapter->addAdminNotice(sprintf('Picup has already been created for this order (ID #%s)', $picupId));
            return;
        }




        $request = DeliveryBucketRequestBuilder::make($order, $picupApiOptions, $this->picupIntegrationDetailsResponse, $zoneId, $deliveryDate);





        $response = $this->picupApi->sendDeliveryBucket($request);




        $this->wordpressAdapter->addPostMeta($orderId, 'picup_bucket_id', $response->getId());
    }

    /**
     * @param $rates
     * @param $package
     *
     * @return mixed
     */
    public function change_shipping_method_name_based_on_shipping_class(
        $rates,
        /** @noinspection PhpUnusedParameterInspection */
        $package
    ) {
        foreach ($rates as $rateKey => $rate) {
            if ($rate->id === 'picup_ondemand_shipping_method') {
                $rates[$rateKey]->label = 'Same-day delivery';
            }

            if ($rate->id === 'picup_scheduled_shipping_method') {
                $rates[$rateKey]->label = 'Fixed-day delivery';
            }

            if ($rate->id === 'picup_scheduled_custom_shipping_method') {
                $rates[$rateKey]->label = 'Fixed-day delivery';
            }
        }

        return $rates;
    }

    /**
     * Override the standard shipping calculator estimate template
     *
     * We need to add an address field to the existing zip/province/country
     *
     * @param string $template
     * @param string $template_name [optional] Template name
     * @param string $template_path [optional] Template path
     *
     * @return string
     */
    public function locate_custom_templates(
        $template,
        /** @noinspection PhpUnusedParameterInspection */
        $template_name,
        /** @noinspection PhpUnusedParameterInspection */
        $template_path
    ): string {
        $basename = basename($template);
        if ($basename === 'shipping-calculator.php') {
            $template = trailingslashit(plugin_dir_path(__FILE__)) . '/../templates/shipping-calculator.php';
        }

        return $template;
    }

    /**
     * We added an address field to the shipping estimator - that value
     * needs to be saved in the database so it persists to the checkout
     */
    public function save_extra_shipping_estimator_fields(): void
    {
        $area = sanitize_text_field($_REQUEST['calc_shipping_address']) ?? '';

        if ($area) {
            WC()->customer->set_billing_address($area);
            WC()->customer->set_shipping_address($area);
        }
    }
}
