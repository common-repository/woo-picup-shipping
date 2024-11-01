<?php

namespace PicupTechnologies\WooPicupShipping\Builders;

use PicupTechnologies\PicupPHPApi\Factories\ThirdPartyFulfillmentCollectionFactory;
use PicupTechnologies\PicupPHPApi\Objects\ThirdParty\ThirdPartyCollectionCollection;
use PicupTechnologies\PicupPHPApi\Requests\ThirdPartyCollectionRequest;

/**
 * Builds a ThirdPartyCollectionRequest using the Collection stored in the WooCommerce Order Metadata
 *
 * @package PicupTechnologies\WooPicupShipping\Builders
 */
final class ThirdPartyCollectionRequestBuilder
{
    /**
     * Returns a ThirdPartyCollectionRequest created from a the collection stored in the order metadata
     *
     * @param $decodedJsonObject
     *
     * @return ThirdPartyCollectionRequest
     */
    public static function make($decodedJsonObject): ThirdPartyCollectionRequest
    {
        $request = new ThirdPartyCollectionRequest();

        $collection = new ThirdPartyCollectionCollection();
        $collection->setCourierCode($decodedJsonObject->courier_code);
        $collection->setServiceType($decodedJsonObject->service_type);

        $thirdPartyCollections = ThirdPartyFulfillmentCollectionFactory::make($decodedJsonObject->collection[0]);
        $collection->setCollections([$thirdPartyCollections]);

        $request->setCollection($collection);

        return $request;
    }
}
