<?php

namespace PicupTechnologies\WooPicupShipping\Builders;

use GuzzleHttp\Client;
use PicupTechnologies\PicupPHPApi\PicupApi;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;

/**
 * Builds a PicupApi instance using the PicupApiOptions
 *
 * We need to build an API with both testing + live keys and set
 * live mode if necessary
 *
 * @package PicupTechnologies\WooPicupShipping\Builders
 */
final class PicupApiBuilder
{
    public static function make(PicupApiOptions $picupApiOptions): PicupApi
    {
        $client = new Client();
        $picupApi = new PicupApi($client, $picupApiOptions->getApiKey(), $picupApiOptions->getTestApiKey());

        if ($picupApiOptions->isLiveMode()) {
            $picupApi->setLive();
        }

        return $picupApi;
    }
}
