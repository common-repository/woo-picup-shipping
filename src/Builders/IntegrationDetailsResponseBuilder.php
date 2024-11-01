<?php

namespace PicupTechnologies\WooPicupShipping\Builders;

use PicupTechnologies\PicupPHPApi\Objects\Parcel;
use PicupTechnologies\PicupPHPApi\Objects\Warehouses\DeliveryWarehouse;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryIntegrationDetailsResponse;

/**
 * Builds a IntegrationDetailsResponse back again
 * from the wordpress-stored settings
 *
 * @package PicupTechnologies\WooPicupShipping\Builders
 */
final class IntegrationDetailsResponseBuilder
{
    /**
     * @param $data
     *
     * @return DeliveryIntegrationDetailsResponse
     */
    public static function make($data): DeliveryIntegrationDetailsResponse
    {
        $response = new DeliveryIntegrationDetailsResponse(
            $data['valid_api_key'],
            '',
            self::makeWarehouses($data['warehouses']),
            self::makeParcels($data['parcel_sizes'])
        );

        return $response;
    }

    /**
     * @param $data
     *
     * @return Parcel[]
     */
    public static function makeParcels($data): array
    {
        $parcels = [];

        foreach ($data as $parcelId => $parcelName) {
            $object = new Parcel($parcelId, $parcelName);
            $parcels[] = $object;
        }

        return $parcels;
    }

    /**
     * @param $data
     *
     * @return DeliveryWarehouse[]
     */
    public static function makeWarehouses($data): array
    {
        $warehouses = [];

        foreach ($data as $dataId => $dataWarehouse) {
            $object = new DeliveryWarehouse($dataId, $dataWarehouse);
            $warehouses[] = $object;
        }

        return $warehouses;
    }
}
