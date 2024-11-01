<?php

declare(strict_types=1);

namespace PicupTechnologies\PicupPHPApi\Objects;

/**
 * Class OrderStatus
 *
 * @package PicupTechnologies\PicupPHPApi\Objects
 */
final class OrderStatus
{

    /**
     * @var ParcelStatus[]
     */
    private $parcelStatuses;

    /**
     * OrderStatus constructor.
     *
     * @param string         $customerReference
     * @param string         $orderStatus
     * @param ParcelStatus[] $parcelStatuses
     */
    public function __construct(array $parcelStatuses)
    {
        $this->parcelStatuses = $parcelStatuses;
    }



   
    /**
     * @return ParcelStatus[]
     */
    public function getParcelStatuses() : array
    {
        return $this->parcelStatuses;
    }
}
