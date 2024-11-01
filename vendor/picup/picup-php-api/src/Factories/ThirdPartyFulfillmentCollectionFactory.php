<?php

namespace PicupTechnologies\PicupPHPApi\Factories;

use DateTime;
use PicupTechnologies\PicupPHPApi\Objects\ThirdParty\ThirdPartyFulfillmentCollection;

final class ThirdPartyFulfillmentCollectionFactory
{
    /**
     * @param $decodedJsonObject
     *
     * @return ThirdPartyFulfillmentCollection
     */
    public static function make($decodedJsonObject): ThirdPartyFulfillmentCollection
    {


        $response = new ThirdPartyFulfillmentCollection();
        $response->setBucketId($decodedJsonObject->bucket_id);
        $response->setCollectionDate($decodedJsonObject->collection_date);
        $response->setCollectionStartTime($decodedJsonObject->collection_start_time);
        $response->setCollectionEndTime($decodedJsonObject->collection_end_time);
        $response->setDeliveryDate($decodedJsonObject->delivery_date);
        $response->setCollectionReference($decodedJsonObject->collection_reference);
        $response->setBusinessId($decodedJsonObject->business_id);
        $response->setUserId($decodedJsonObject->user_id);
        $response->setWarehouseId($decodedJsonObject->warehouse_id);
        $response->setWaybill(ThirdPartyWaybillFactory::make($decodedJsonObject->waybill));

        return $response;
    }
}
