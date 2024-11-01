<?php

declare(strict_types=1);

namespace PicupTechnologies\PicupPHPApi\Responses;

/**
 * Holds the DeliveryOrder response from Picup after creating an order.
 *
 * @package PicupTechnologies\PicupPHPApi\Responses
 */
class DeliveryOrderResponse
{
    /**
     * @var string Picup Id
     */
    private $requestId;

    /**
     * DeliveryOrderResponse constructor.
     *
     * @param string $picupId
     */
    public function __construct(?string $picupId)
    {
        $this->picupId = $picupId;
    }

    public function getId() : ?string
    {   
        return $this->picupId;
    }
}
