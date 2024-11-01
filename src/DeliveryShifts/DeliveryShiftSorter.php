<?php
/**
 * Created by PhpStorm.
 * User: bryan
 * Date: 2019/02/18
 * Time: 10:47 AM
 */

namespace PicupTechnologies\WooPicupShipping\DeliveryShifts;

/**
 * Class DeliveryShiftSorter
 *
 * Responsible for sorting a DeliveryShift collection with reference to a particular date.
 *
 * eg Shifts are:
 * 1. Monday 09:00 to 10:00
 * 2. Tues   09:00 to 10:00
 *
 * Today is Monday 10:00
 *
 * Shifts must be:
 * 1. Tues
 * 1. Next Monday 09:00
 */
final class DeliveryShiftSorter
{
    /**
     * @param DeliveryShiftCollection $deliveryShiftCollection
     *
     * @return DeliveryShiftCollection
     */
    public function sortShifts(DeliveryShiftCollection $deliveryShiftCollection): DeliveryShiftCollection
    {
        $shifts = $deliveryShiftCollection->getShifts();

        // now we simply sort it by date
        uasort($shifts, static function ($a, $b) {
            /** @var DeliveryShift $a */
            /** @var DeliveryShift $b */
            return $a->getShiftStartDate() > $b->getShiftStartDate();
        });

        $sortedCollection = new DeliveryShiftCollection();
        $sortedCollection->setShifts($shifts);

        return $sortedCollection;
    }
}
