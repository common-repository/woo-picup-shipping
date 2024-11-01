<?php

namespace PicupTechnologies\WooPicupShipping\Classes;

use PicupTechnologies\PicupPHPApi\Collections\ParcelCollection;

/**
 * Contains the parcels returned from the integration details response
 * and saved into picup_parcels
 *
 * @package PicupTechnologies\WooPicupShipping\Classes
 */
final class PicupParcels
{
    /**
     * @var ParcelCollection
     */
    private $parcels;

    /**
     * Builds the PicupParcels object containing the parcels returned
     * from the picup api
     *
     * @param $data
     *
     * @return PicupParcels
     */
    public static function make($data): PicupParcels
    {

    }
}
