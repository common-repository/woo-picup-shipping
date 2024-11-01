<?php

declare(strict_types=1);

namespace PicupTechnologies\PicupPHPApi\Factories;

use PicupTechnologies\PicupPHPApi\Objects\OrderStatus;
use PicupTechnologies\PicupPHPApi\Responses\OrderStatusResponse;

/**
 * Builds an OrderStatusResponse
 *
 * @package PicupTechnologies\PicupPHPApi\Factories
 */
final class OrderStatusResponseFactory
{
    /**
     * @param array $body
     *
     * @return OrderStatusResponse
     */
    public static function  make(array $body)
    {
        $orderStatuses = [];

        foreach ($body as $item) {
            // build parcels
            $parcelStatuses = ParcelStatusFactory::make($item);

            $orderStatuses[] = new OrderStatus(
                $parcelStatuses
            );
        }


        return new OrderStatusResponse($orderStatuses);
    }
}
