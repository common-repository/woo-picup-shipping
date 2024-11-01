<?php

declare(strict_types=1);

namespace PicupTechnologies\PicupPHPApi\Requests;

use JsonSerializable;
use PicupTechnologies\PicupPHPApi\Contracts\PicupRequestInterface;

/**
 * Class OrderStatusRequest
 *
 * @package PicupTechnologies\PicupPHPApi\Requests
 */
final class OrderStatusRequest implements PicupRequestInterface, JsonSerializable
{
    /**
     * @var mixed
     */
    private $picupId;

     /**
     * Specify data which should be serialized to JSON
     *
     * @see  https://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return  $this->picupId;
    }

  
}
