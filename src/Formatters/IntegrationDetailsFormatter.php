<?php

namespace PicupTechnologies\WooPicupShipping\Formatters;

use PicupTechnologies\PicupPHPApi\Objects\Parcel;
use PicupTechnologies\PicupPHPApi\Objects\Warehouses\DeliveryWarehouse;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryIntegrationDetailsResponse;

/**
 * Responsible for taking the IntegrationDetails Response from Picup Enterprise
 * and converting it into a format Wordpress can update the database with
 *
 * @package PicupTechnologies\WooPicupShipping\Formatters
 */
final class IntegrationDetailsFormatter
{
    public static function format(DeliveryIntegrationDetailsResponse $deliveryIntegrationDetailsResponse): array
    {
        $options = [
            'valid_api_key'              => $deliveryIntegrationDetailsResponse->isKeyValid(),
            'estimation_validity_length' => 86400,
            'parcel_sizes'               => self::formatParcels($deliveryIntegrationDetailsResponse->getParcels()),
            'warehouses'                 => self::formatWarehouses($deliveryIntegrationDetailsResponse->getWarehouses()),
        ];

        return $options;
    }

    /**
     * Formats the parcels into array that we store to db
     *
     * @param Parcel[] $parcels
     *
     * @return array
     */
    public static function formatParcels(array $parcels): array
    {
        $formatted = [];
        $formatted['custom'] = 'Custom';

        foreach ($parcels as $parcel) {
            $formatted[$parcel->getId()] = $parcel->getDescription();
        }

        return $formatted;
    }

    /**
     * @param DeliveryWarehouse[] $warehouses
     *
     * @return array
     */
    public static function formatWarehouses(array $warehouses): array
    {
        $formatted = [];
        foreach ($warehouses as $warehouse) {
            $formatted[$warehouse->getId()] = $warehouse->getName();
        }

        return $formatted;
    }
}
