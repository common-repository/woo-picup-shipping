<?php

namespace PicupTechnologies\WooPicupShipping\Builders;

use DateTime;
use Exception;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryBucket\DeliveryBucketDetails;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryBucket\DeliveryShipment;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryBucket\DeliveryShipmentAddress;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryBucket\DeliveryShipmentContact;
use PicupTechnologies\PicupPHPApi\Requests\DeliveryBucketRequest;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryIntegrationDetailsResponse;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use PicupTechnologies\WooPicupShipping\DeliveryShifts\DeliveryShiftCollection;
use WC_Order;

/**
 * Builds a DeliveryBucketRequest
 *
 * @package PicupTechnologies\WooPicupShipping\Builders
 */
final class DeliveryBucketRequestBuilder
{
    /**
     * @param WC_Order $order
     * @param PicupApiOptions $picupApiOptions
     * @param DeliveryIntegrationDetailsResponse $deliveryIntegrationDetailsResponse
     * @param int $zoneId
     *
     * @param DateTime|null $customDeliveryDate Custom date for PicupScheduledCustom
     *
     * @return DeliveryBucketRequest
     * @throws Exception
     */
    public static function make(WC_Order $order, PicupApiOptions $picupApiOptions, DeliveryIntegrationDetailsResponse $deliveryIntegrationDetailsResponse, int $zoneId, DateTime $customDeliveryDate = null): DeliveryBucketRequest
    {
        // FETCH DATA
        $receiver = $order->get_address('shipping');
        $receiver['country'] = WC()->countries->countries[ $order->get_shipping_country() ];


        // BUILD REQUEST
        $bucketRequest = new DeliveryBucketRequest();

        $bucketDetails = new DeliveryBucketDetails();
        if ($zoneSettings = $picupApiOptions->getWarehouseSettings()->getZone($zoneId)) {
            $bucketDetails->setWarehouseId($zoneSettings->getWarehouseId());
        }

        // SET DELIVERY DATE
        if (! $customDeliveryDate) {
            $shifts = unserialize(get_option('picup_scheduled_shifts'));

            $selectedShift = $order->get_meta('picup_scheduled_selected_shift');
            // build shift collection
            $currentDateTime = new \DateTime();
            $deliveryShiftCollection = DeliveryShiftCollection::buildFromWordpressData($shifts, $currentDateTime);

            // return the selected shift
            $actualShiftUserSelected = $deliveryShiftCollection->getShift($selectedShift);

            $bucketDetails->setDeliveryDate($actualShiftUserSelected->getShiftStartDate());
            $bucketDetails->setShiftStart($actualShiftUserSelected->getShiftStartDate());
            $bucketDetails->setShiftEnd($actualShiftUserSelected->getShiftEndDate());
        } else {
            $bucketDetails->setDeliveryDate($customDeliveryDate);

            $customDeliveryDate->setTime(8, 0, 0);
            $bucketDetails->setShiftStart($customDeliveryDate);

            $customDeliveryDate->setTime(17, 0, 0);
            $bucketDetails->setShiftEnd($customDeliveryDate);
        }

        $bucketRequest->setBucketDetails($bucketDetails);

        $shipment = new DeliveryShipment();
        $shipment->setBusinessReference(trim(str_replace('#', '', $order->get_order_number())));        
        // $shipment->setConsignment('consignment-' . $order->get_id());
        $address = new DeliveryShipmentAddress();
        // $address->setAddressReference($receiver['address_1']);
        $address->setAddressLine1($receiver['address_1']);
        $address->setCity($receiver['city']);
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

        $bucketRequest->setShipments([$shipment]);

        return $bucketRequest;
    }
}
