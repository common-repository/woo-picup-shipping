<?php

declare(strict_types=1);

namespace PicupTechnologies\PicupPHPApi\Requests;

use DateTime;
use JsonSerializable;
use PicupTechnologies\PicupPHPApi\Contracts\PicupRequestInterface;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryReceiver;
use PicupTechnologies\PicupPHPApi\Objects\DeliverySender;

/**
 * Class DeliveryQuoteRequest
 *
 * Holds all the details for a DeliveryQuoteRequest
 *
 * @package PicupTechnologies\PicupPHPApi\Requests
 */
final class DeliveryQuoteRequest implements PicupRequestInterface, JsonSerializable
{
    /**
     * Merchant UUID
     *
     * @var string
     */
    private $merchantId;

    /**
     * Customer Reference
     *
     * @var string
     */
    private $customerRef;

    private $isForContractDriver = false;

    private $userId = '';
    private $courierCosting = 'ALL';

    /**
     * Scheduled date
     *
     * @var DateTime
     */
    private $scheduledDate;

    /**
     * @var DeliverySender
     */
    private $sender;

    /**
     * @var DeliveryReceiver[]
     */
    private $receivers;

    /**
     * @var bool Whether the quote must optimize waypoints or not
     */
    private $optimizeWaypoints = true;

    public function getMerchantId() : string
    {
        return $this->merchantId;
    }

    public function setMerchantId(string $merchantId) : void
    {
        $this->merchantId = $merchantId;
    }

    public function getCustomerRef() : string
    {
        return $this->customerRef;
    }

    public function setCustomerRef(string $customerRef) : void
    {
        $this->customerRef = $customerRef;
    }

    public function isForContractDriver() : bool
    {
        return $this->isForContractDriver;
    }

    public function setIsForContractDriver(bool $isForContractDriver) : void
    {
        $this->isForContractDriver = $isForContractDriver;
    }

    public function getUserId() : string
    {
        return $this->userId;
    }

    public function setUserId(string $userId) : void
    {
        $this->userId = $userId;
    }

    public function getScheduledDate() : DateTime
    {
        return $this->scheduledDate;
    }

    public function setScheduledDate(DateTime $scheduledDate) : void
    {
        $this->scheduledDate = $scheduledDate;
    }

    public function getSender() : DeliverySender
    {
        return $this->sender;
    }

    public function setSender(DeliverySender $sender) : void
    {
        $this->sender = $sender;
    }

    /**
     * @return DeliveryReceiver[]
     */
    public function getReceivers() : array
    {
        return $this->receivers;
    }

    /**
     * @param DeliveryReceiver[] $receivers
     */
    public function setReceivers(array $receivers) : void
    {
        $this->receivers = $receivers;
    }

    public function addReceiver(DeliveryReceiver $receiver) : void
    {
        $this->receivers[] = $receiver;
    }

    public function isOptimizeWaypoints() : bool
    {
        return $this->optimizeWaypoints;
    }

    public function setOptimizeWaypoints(bool $optimizeWaypoints) : void
    {
        $this->optimizeWaypoints = $optimizeWaypoints;
    }

    public function enableThirdPartyCouriers() : void
    {
        $this->courierCosting = 'ALL';
    }

    public function enableContractDrivers() : void
    {
        $this->isForContractDriver = true;
    }

    



    public function disableThirdPartyCouriers() : void
    {
        $this->courierCosting = 'NONE';
    }

    /**
     * @return string
     */
    public function getCourierCosting(): string
    {
        return $this->courierCosting;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @see  http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'merchant_id' => $this->merchantId,
            'user_id' => $this->userId,
            'customer_ref' => $this->customerRef,
            'scheduled_date' => $this->scheduledDate->format('Y-m-d\TH:i:s.u\Z'),
            'courier_costing' => $this->courierCosting,
            'sender' => $this->sender,
            'receivers' => $this->receivers,
            'optimize_waypoints' => $this->optimizeWaypoints
        ];
    }
}
