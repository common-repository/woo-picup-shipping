<?php

declare(strict_types=1);

namespace PicupTechnologies\PicupPHPApi\Objects;

/**
 * Class ParcelStatus
 *
 * @package PicupTechnologies\PicupPHPApi\Objects
 */
final class ParcelStatus
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $timestamp;

    /**
     * @var string
     */
    private $reference_number;

    /**
     * @var string
     */
    private $business_reference;
    
    /**
     * ParcelStatus constructor.
     *
     * @param string $reference
     * @param string $status
     * @param string $trackingNumber
     */

    public function __construct(string $status, string $timestamp, string $reference_number)
    {
        $this->status = $status;
        $this->timestamp = $timestamp;
        $this->reference_number = $reference_number;
    }

    public function getStatus() : string
    {
        return $this->status;
    }
    public function getTimestamp() : string
    {
        return $this->timestamp;
    }
    public function getReferenceNumber() : string
    {
        return $this->reference_number;
    }
    public function getBusinessReference() : string
    {
        return $this->business_reference;
    }

}
