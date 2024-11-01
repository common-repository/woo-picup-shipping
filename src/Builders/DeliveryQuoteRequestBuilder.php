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
use PicupTechnologies\PicupPHPApi\Objects\Warehouses\DeliveryWarehouse;
use PicupTechnologies\PicupPHPApi\Requests\DeliveryQuoteRequest;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryIntegrationDetailsResponse;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use PicupTechnologies\WooPicupShipping\Classes\PicupZone;

/**
 * Builds a DeliveryQuoteRequest
 *
 * @package PicupTechnologies\WooPicupShipping\Builders
 */
final class DeliveryQuoteRequestBuilder
{
    /**
     * Takes the POSTed data from the Woocommerce Cart and
     * builds a DeliveryQuoteRequest
     *
     * @param                                    $args
     * @param DeliveryIntegrationDetailsResponse $deliveryIntegrationDetailsResponse
     * @param PicupApiOptions                    $picupApiOptions
     * @param PicupZone                          $picupZone
     *
     * @return DeliveryQuoteRequest
     * @throws Exception
     */
    public static function build($args, DeliveryIntegrationDetailsResponse $deliveryIntegrationDetailsResponse, PicupApiOptions $picupApiOptions, PicupZone $picupZone): DeliveryQuoteRequest
    {
        $request = new DeliveryQuoteRequest();

        if ($picupApiOptions->isThirdPartyCouriersEnabled()) {
            $request->enableThirdPartyCouriers();
        }

        if ($picupApiOptions->isUseContractDrivers()) {
            $request->enableContractDrivers();
        }



        // BASICS
        $scheduledDate = new DateTime();
        // $scheduledDate->modify('+1 day');
        $request->setScheduledDate($scheduledDate);

        $request->setCustomerRef(md5($scheduledDate->getTimestamp()));
        $request->setMerchantId('merchant-d827f668-d434-4ce5-b853-878f874ae746');

        //randomize parcels for quote
        $order_id = rand(1, 1000) . rand(1, 1000);
        $last_name = rand(1, 1000) . rand(1, 1000);

        $parcels = ParcelBuilder::build($order_id, $last_name, $args['contents'], $deliveryIntegrationDetailsResponse);

        // RECEIVER
        $receiverAddress = new DeliveryReceiverAddress();

        if (isset($args['destination']['address_2'])) {
            $receiverAddress->setUnitNo($args['destination']['address_2']);
        }

        if (isset($args['destination']['address'])) {
            $receiverAddress->setStreetOrFarm($args['destination']['address']);
        }

        $receiverAddress->setCity($args['destination']['city']);
        $receiverAddress->setPostalCode($args['destination']['postcode']);
        $receiverAddress->setCountry($args['destination']['country']);

        $contact = new DeliveryReceiverContact();
        $contact->setName('Woo Quote');
        $contact->setCellphone('021 123 4567');

        $receiver = new DeliveryReceiver(
            $receiverAddress,
            $contact,
            $parcels,
            ''
        );

        $request->addReceiver($receiver);

        // SENDER
        $senderAddress = new DeliverySenderAddress();
        $senderAddress->setWarehouseId($picupZone->getWarehouseId());

        $senderContact = new DeliverySenderContact();
        $senderContact->setName($picupApiOptions->getName());
        $senderContact->setEmail($picupApiOptions->getEmail());
        $senderContact->setTelephone($picupApiOptions->getTelephone());
        $senderContact->setCellphone($picupApiOptions->getCellphone());

        $specialInstructions = $picupApiOptions->getSpecialInstructions();

        $sender = new DeliverySender(
            $senderAddress,
            $senderContact,
            $specialInstructions
        );

        $request->setSender($sender);

        return $request;
    }
}
