<?php

namespace PicupTechnologies\WooPicupShipping\ShippingMethods;

use Exception;
use PicupTechnologies\PicupPHPApi\Exceptions\PicupApiException;
use PicupTechnologies\PicupPHPApi\Exceptions\PicupRequestFailed;
use PicupTechnologies\PicupPHPApi\Exceptions\ValidationException;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryIntegrationDetailsResponse;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryQuoteResponse;
use PicupTechnologies\WooPicupShipping\Adapters\WordpressAdapter;
use PicupTechnologies\WooPicupShipping\Builders\DeliveryQuoteRequestBuilder;
use PicupTechnologies\WooPicupShipping\Builders\IntegrationDetailsResponseBuilder;
use PicupTechnologies\WooPicupShipping\Builders\PicupApiBuilder;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use PicupTechnologies\WooPicupShipping\Classes\WooCommerceRate;
use RuntimeException;
use WC_Shipping_Method;
use WC_Shipping_Zones;

/**
 * PicupOnDemand Shipping Method
 *
 * Fetches the OnDemand rate from Picup and returns it to the user
 *
 * @package PicupTechnologies\WooPicupShipping\ShippingMethods
 */
final class PicupOnDemand extends WC_Shipping_Method
{
    /**
     * @var WordpressAdapter
     */
    private $wordpressAdapter;

    /**
     * @var DeliveryIntegrationDetailsResponse
     */
    private $integrationDetails;

    /**
     * PicupOnDemand constructor.
     *
     * @param int $instanceId
     */
    public function __construct($instanceId = 0)
    {
        parent::__construct($instanceId);

        $this->id = 'picup_ondemand_shipping_method';
        $this->instance_id = absint($instanceId);
        $this->method_title = __('Picup OnDemand');
        $this->method_description = __('OnDemand Shipping Method API for the Picup delivery service');
        $this->title = 'Picup OnDemand Shipping';
        $this->supports = [
            'shipping-zones',
        ];
        $this->enabled = 'yes';
        $this->tax_status = 'taxable';

        $this->wordpressAdapter = new WordpressAdapter();
        $data = $this->wordpressAdapter->getOption('picup_integration');

        if (empty($data)) {
            return;
        }

        $this->integrationDetails = IntegrationDetailsResponseBuilder::make($data);
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
     * Fetches the OnDemand Shipping Rate from Picup
     *
     * Due to this class not being inherited from the main plugin we have
     * to re-instantiate most of what we need again.
     *
     * @param array $args Shipping rate request as provided by Woocommerce
     */
    public function calculate_shipping($args = []): void
    {
        if ($this->freeShippingValid()) return;
        $picupApiOptions = PicupApiOptions::buildFromWordpress($this->wordpressAdapter->getOption('picup-api-options'));
        $picupApi = PicupApiBuilder::make($picupApiOptions);

        // Shipping is calculated even when the user first adds to cart.
        // This would mean the user has not entered their address yet most likely
        $destination = $args['destination'];
        if (empty($destination['city']) || empty($destination['postcode']) || empty($destination['country'])) {
            return;
        }

        try {
            // We must now work out which shipping zone the shopper is in so we can
            // provide the correct warehouse for the sender
            $shippingZone = WC_Shipping_Zones::get_zone_matching_package($args);
            $picupZoneSettings = $picupApiOptions->getWarehouseSettings();
            $warehouseZone = $picupZoneSettings->getZone($shippingZone->get_id());
            if (!$warehouseZone) {
                throw new RuntimeException('No warehouse set for shipping zone.');
            }

            // now lets handle the actual quote
            $quoteRequest = DeliveryQuoteRequestBuilder::build($args, $this->integrationDetails, $picupApiOptions, $warehouseZone);
            $quoteResponse = $picupApi->sendQuoteRequest($quoteRequest);

            // add the picup rate
            $this->addPicupRate($quoteResponse, $picupApiOptions);

            // add any third party rates that may have been returned
           // $this->addThirdPartyCourierRates($quoteResponse, $picupApiOptions);
        } catch (ValidationException $e) {
            $errorMessage = 'Validation errors - ' . print_r($e->getValidationErrors(), true);

            error_log($errorMessage);
            \wc_add_notice($errorMessage, 'notice');
        } catch (Exception $e) {
            $errorMessage = 'Error fetching ondemand shipping rate from shipping calculator - ' . $e->getMessage();

            error_log($errorMessage);
            \wc_add_notice($errorMessage, 'notice');
        }
    }

    /**
     * Adds the Picup Rate if it is valid
     *
     * @param DeliveryQuoteResponse $deliveryQuoteResponse
     * @param PicupApiOptions       $picupApiOptions
     *
     * @throws PicupApiException
     */
    private function addPicupRate(DeliveryQuoteResponse $deliveryQuoteResponse, PicupApiOptions $picupApiOptions): void
    {
        if (!$deliveryQuoteResponse->isValid()) {
            return;
        }

        // ok send the rate to woocommerce
        $rate = WooCommerceRate::make($deliveryQuoteResponse);
        $this->add_rate($rate->toArray());
    }

    /**
     * Adds the Third Party Courier rates if its valid
     *
     * @param DeliveryQuoteResponse $deliveryQuoteResponse
     * @param PicupApiOptions       $picupApiOptions
     *
     * @return void
     */
    private function addThirdPartyCourierRates(DeliveryQuoteResponse $deliveryQuoteResponse, PicupApiOptions $picupApiOptions): void
    {
        // Third party couriers are disabled in the options
        if (!$picupApiOptions->isThirdPartyCouriersEnabled()) {
            return;
        }

        $thirdPartyResponse = $deliveryQuoteResponse->getThirdPartyResponse();
        if ($thirdPartyResponse === null) {
            return;
        }

        if (!$thirdPartyResponse->isValid()) {
            return;
        }

        if (empty($thirdPartyResponse->getFulfillmentOptions())) {
            throw new RuntimeException('Received a third party response but did not receive fulfillment options');
        }

        // Set third party flag in session
        $_SESSION['third_party'] = true;
        foreach ($thirdPartyResponse->getFulfillmentOptions() as $fulfillmentOption) {
            $rate = new WooCommerceRate();

            $rate->setId($fulfillmentOption->getDescription());
            $label = sprintf(
                '%s Courier (delivered by: %s)',
                $fulfillmentOption->getDescription(),
                $fulfillmentOption->getDeliveredBefore()->format('j M Y - H:i')
            );
            $rate->setLabel($label);
            $rate->setCost($fulfillmentOption->getPriceIncVat());
            $rate->setCalcTax('per_order');

            $this->add_rate($rate->toArray());

            // we need to store all the third party responses in the session because
            // we need to send the full response to the create collection endpoint and
            // we dont know which method the user has chosen
            $option = strtolower($fulfillmentOption->getDescription()); // eg, fastest
            $_SESSION['third_party_responses'][$option] = json_encode($fulfillmentOption->getCollections());
        }
    }
}
