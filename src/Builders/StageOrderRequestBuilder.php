<?php
namespace PicupTechnologies\WooPicupShipping\Builders;


use DateTime;
use Exception;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryIntegrationDetailsResponse;
use PicupTechnologies\PicupPHPApi\Requests\StageOrderRequest;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryBucket\DeliveryShipment;
use PicupTechnologies\PicupPHPApi\Objects\GenericAddress;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryBucket\DeliveryShipmentContact;
use WC_Order;

final class StageOrderRequestBuilder
{
    public static function make(WC_Order $order, PicupApiOptions $picupApiOptions, DeliveryIntegrationDetailsResponse $deliveryIntegrationDetailsResponse, int $zoneId, DateTime $customDeliveryDate = null): StageOrderRequest
    {

        // FETCH DATA
        $receiver = $order->get_address('shipping');
        $receiver['country'] = WC()->countries->countries[ $order->get_shipping_country() ];

        // BUILD REQUEST
        $stageOrder = new StageOrderRequest();

        if ($zoneSettings = $picupApiOptions->getWarehouseSettings()->getZone($zoneId)) {
            $stageOrder->setWarehouseId($zoneSettings->getWarehouseId());
        }

        $shipment = new DeliveryShipment();
        $shipment->setBusinessReference(trim(str_replace('#', '', $order->get_order_number())));
        // $shipment->setConsignment('consignment-' . $order->get_id());
        $address = new GenericAddress();
        // $address->setAddressReference($receiver['address_1']);
        $address->setStreetOrFarmNo($receiver['address_1']." ".$receiver["address_2"]);
        $address->setStreetOrFarm($receiver['address_1']);
        $address->setCity($receiver['city']);
        $address->setPostalCode($receiver['postcode']);
        $address->setCountry($receiver['country']);
        $shipment->setAddress($address);

        $contact = new DeliveryShipmentContact();
        $contact->setCustomerName($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name());
        $contact->setCustomerPhone($order->get_billing_phone());
        $contact->setEmailAddress($order->get_billing_email());
        $contact->setSpecialInstructions($order->get_customer_note());
        $shipment->setContact($contact);

        $last_name = $order->get_shipping_last_name();
        $order_id = trim(str_replace('#', '', $order->get_order_number()));

        $parcels = ParcelBuilder::build($order_id, $last_name,  $order->get_items(), $deliveryIntegrationDetailsResponse);
        $shipment->setParcelCollection($parcels);

        $stageOrder->addShipment($shipment);





        return $stageOrder;
    }
}
