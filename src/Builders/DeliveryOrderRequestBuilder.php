<?php

namespace PicupTechnologies\WooPicupShipping\Builders;

use DateTime;
use Exception;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryReceiver;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryReceiverAddress;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryReceiverContact;
use PicupTechnologies\PicupPHPApi\Objects\DeliverySender;
use PicupTechnologies\PicupPHPApi\Objects\DeliverySenderAddress;
use PicupTechnologies\PicupPHPApi\Objects\DeliverySenderContact;
use PicupTechnologies\PicupPHPApi\Requests\DeliveryOrderRequest;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryIntegrationDetailsResponse;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use WC_Order;

/**
 * Builds a DeliveryOrderRequest
 *
 * @package PicupTechnologies\WooPicupShipping\Builders
 */
final class DeliveryOrderRequestBuilder
{
    /**
     * Takes a WoocommerceOrder with PicupApiOptions and builds
     * @paramDeliveryOrderRequest
     * @param string                             $onDemandPrepTime
     * @param WC_Order                           $order
     * @param PicupApiOptions                    $picupApiOptions
     * @param DeliveryIntegrationDetailsResponse $deliveryIntegrationDetailsResponse
     * @param int                                $zoneId
     *
     * @return DeliveryOrderRequest
     * @throws Exception
     */
    public static function make(WC_Order $order, PicupApiOptions $picupApiOptions, DeliveryIntegrationDetailsResponse $deliveryIntegrationDetailsResponse, int $zoneId): DeliveryOrderRequest
    {
        $request = new DeliveryOrderRequest();

        $scheduledDate = new DateTime();
        $onDemandPrepTime = $picupApiOptions->getOnDemandPrepTime();
        $isContractDriverEnabled = $picupApiOptions->isUseContractDrivers();

        if(isset($onDemandPrepTime)){
            $scheduledDate->modify('+' . $onDemandPrepTime  . ' minutes');
        }


        $request->setIsForContractDriver($isContractDriverEnabled);
        $request->setMerchantId('merchant-d827f668-d434-4ce5-b853-878f874ae746');
        $request->setScheduledDate($scheduledDate);
        $request->setCustomerRef($order->get_id());

        // SET SENDER
        $senderAddress = new DeliverySenderAddress();
        if ($zoneSettings = $picupApiOptions->getWarehouseSettings()->getZone($zoneId)) {
            $senderAddress->setWarehouseId($zoneSettings->getWarehouseId());
        }

        $senderContact = new DeliverySenderContact();
        $senderContact->setName($picupApiOptions->getName());
        $senderContact->setEmail($picupApiOptions->getEmail());
        $senderContact->setCellphone($picupApiOptions->getCellphone());
        $senderContact->setTelephone($picupApiOptions->getTelephone());

        $senderSpecialInstructions = '';

        $sender = new DeliverySender($senderAddress, $senderContact, $senderSpecialInstructions);
        $request->setSender($sender);

        // SET RECEIVER
        $shippingAddress = $order->get_address('shipping');

        $receiverAddress = new DeliveryReceiverAddress();
        $receiverAddress->setComplex($shippingAddress['address_1']);
        $receiverAddress->setCity($shippingAddress['city']);
        $receiverAddress->setPostalCode($shippingAddress['postcode']);
        $receiverAddress->setCountry($shippingAddress['country']);

        $receiverContact = new DeliveryReceiverContact();
        $receiverContact->setName($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name());

        if ($order->get_shipping_first_name() !== null && $order->get_shipping_last_name() !== null) {
            $receiverContact->setName($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name());
        }

        $receiverContact->setEmail($order->get_billing_email());
        $receiverContact->setTelephone($order->get_billing_phone());
        $receiverContact->setCellphone($order->get_billing_phone());

        $items = $order->get_items();
        $order_id = trim(str_replace('#', '', $order->get_order_number()));
        $last_name = $order->get_shipping_last_name();

        $receiverParcels = ParcelBuilder::build($order_id, $last_name, $items, $deliveryIntegrationDetailsResponse);

        $specialInstructions = $order->get_customer_note();

        $receiver = new DeliveryReceiver(
            $receiverAddress,
            $receiverContact,
            $receiverParcels,
            $specialInstructions
        );

        $request->setReceivers([$receiver]);

        return $request;
    }
}
