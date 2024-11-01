<?php

namespace PicupTechnologies\WooPicupShipping\Builders;

use PicupTechnologies\PicupPHPApi\Builders\SmallestParcelBuilder;
use PicupTechnologies\PicupPHPApi\Collections\ParcelCollection;
use PicupTechnologies\PicupPHPApi\Objects\Parcel;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryIntegrationDetailsResponse;
use PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions;
use PicupTechnologies\WooPicupShipping\Adapters\WordpressAdapter;

/**
 * Builds a DeliveryParcelCollection
 *
 * @package PicupTechnologies\WooPicupShipping\Builders
 */



final class ParcelBuilder
{
    /**
     * Takes the items in a users Woo commerce Cart and builds
     * a ParcelCollection containing Parcels.
     *
     * If the product has a specific parcel size set we use that.
     *
     * If the product only has dimensions set we must find the smallest
     * parcel that fits the product.
     *
     * @param                                    $items
     * @param DeliveryIntegrationDetailsResponse $deliveryIntegrationDetailsResponse
     *
     * @return ParcelCollection
     */

    /**
     * Holds all the options used by the plugin and stored in the database
     *
     * @var PicupApiOptions
     */

   /**
     * @var WordpressAdapter
     */

    public static function build($order_id, $last_name, $items, DeliveryIntegrationDetailsResponse $deliveryIntegrationDetailsResponse): ParcelCollection
    {
        $parcelCollection = new ParcelCollection();
        $wordpressAdapter = new WordpressAdapter();
        $globalItemCount = 0;
        $picupApiOptions = PicupApiOptions::buildFromWordpress($wordpressAdapter->getOption('picup-api-options'));
        $isConsolidateBoxes = $picupApiOptions->getIsConsolidateBoxes();

        if ($isConsolidateBoxes) {

            $consolidatedBoxSize = $picupApiOptions->getConsolidatedBoxSize();
            $consolidatedItemsPerBox = $picupApiOptions->getConsolidatedItemsPerBox();

            $itemCount = 0;
            foreach ($items as $item_id => $item) {
                $itemCount +=  $item['quantity'];
            }

            $numberOfBoxes = ceil($itemCount / $consolidatedItemsPerBox);

            for ($item_count = 0; $item_count < $numberOfBoxes; $item_count++) {
                $parcel = new Parcel();
                $parcel->setId($consolidatedBoxSize);
                $parcel->setDescription($order_id . '_' . $last_name . '_' . $item_count);
                $parcel->setReference($order_id . '_' . $last_name . '_' . $item_count);
                $parcel->setTrackingNumber($order_id . $last_name . '_' . $item_count);
                $parcelCollection->addParcel($parcel);
            }

        } else {

            foreach ($items as $item_id => $item) {
                if (isset($item['data'])) {
                    $product = $item['data'];
                    $product_id = $product->get_id();
                } else {
                    $product_id = $item->get_product_id();
                    $product = wc_get_product($product_id);
                }

                $size_metric = '_picup_parcel_size';

                $allDimensionsEmpty = (empty(get_post_meta($product_id, '_width', true)) ||
                    empty(get_post_meta($product_id, '_height', true)) ||
                    empty(get_post_meta($product_id, '_weight', true)) ||
                    empty(get_post_meta($product_id, '_length', true)));

                if ($product->is_type('variation')) {
                    if (
                        (empty(get_post_meta($product_id, '_variable_picup_parcel_size', true)) ||
                            (get_post_meta($product_id, '_variable_picup_parcel_size', true) === 'custom') && $allDimensionsEmpty)
                    ) {
                        $product = wc_get_product($product->get_parent_id());
                        $product_id = $product->get_id();
                    } else {
                        $size_metric = '_variable_picup_parcel_size';
                    }
                }

                for ($item_count = 0; $item_count < $item['quantity']; $item_count++) {
                    $globalItemCount++;
                    if (!empty(get_post_meta($product_id, $size_metric, true)) && get_post_meta($product_id, $size_metric, true) !== 'custom') {

                        // This product has a specific parcel attached to it
                        $parcel = new Parcel();
                        $parcel->setId(get_post_meta($product_id, $size_metric, true));
                        $parcel->setDescription($order_id . '_' . $last_name . '_' . $globalItemCount);
                        $parcel->setReference($order_id . '_' . $last_name . '_' . $globalItemCount);
                        $parcel->setTrackingNumber($order_id . '_' . $last_name . '_' . $globalItemCount);

                        $parcelCollection->addParcel($parcel);
                    } else {
                        if (
                            !empty(get_post_meta($product_id, '_width', true)) &&
                            !empty(get_post_meta($product_id, '_height', true)) &&
                            !empty(get_post_meta($product_id, '_length', true))
                        ) {
                            // Now we look for the smallest parcel to fit the product given the parcels we support
                            $builder = new SmallestParcelBuilder($deliveryIntegrationDetailsResponse->getParcels());

                            $parcel = $builder->find(
                                get_post_meta($product_id, '_height', true),
                                get_post_meta($product_id, '_width', true),
                                get_post_meta($product_id, '_length', true)
                            );

                            $parcel->setReference($order_id . '_' . $last_name . '_' . $globalItemCount);
                            $parcel->setTrackingNumber($order_id . '_' . $last_name . '_' . $globalItemCount);
                            $parcelCollection->addParcel($parcel);
                        }
                    }
                }
            }
        }

        return $parcelCollection;
    }
}
