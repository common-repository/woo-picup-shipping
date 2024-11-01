<?php
/**
 * Created by PhpStorm.
 * User: bryan
 * Date: 2019/02/14
 * Time: 1:32 PM
 */

namespace PicupTechnologies\WooPicupShipping\DeliveryShifts;

use DateTime;
use Exception;
use RuntimeException;

/**
 * Class DeliveryShiftCollection
 *
 * Responsible for storing a collection of DeliveryShifts.
 *
 * This must not sort them because we need to be able to simply fetch
 * a list of shifts for display in the Admin area.
 */
final class DeliveryShiftCollection
{
    /**
     * Stores the shifts
     *
     * @var DeliveryShift[]
     */
    private $shifts;

    /**
     * Factory function to return a new DeliveryShiftCollection from the data
     * obtained from the PicupScheduled shift database.
     *
     * If the shift day is the same as the reference day then we must add 1 week on to it.
     *
     * @param           $shiftData
     *
     * @param DateTime  $referenceDateTime
     *
     * @return DeliveryShiftCollection
     * @throws Exception
     */
    public static function buildFromWordpressData($shiftData, DateTime $referenceDateTime): self
    {
        $collection = new self();



        foreach ($shiftData as $shiftKey => $shift) {
            $newShift = new DeliveryShift(
                $shiftKey,
                $shift['day'],
                new DateTime($shift['start_time']),
                new DateTime($shift['end_time']),
                $referenceDateTime
            );

            $collection->addShift($newShift);
        }

        return $collection;
    }

    /**
     * Adds a shift to the collection
     *
     * @param DeliveryShift $shift
     */
    public function addShift(DeliveryShift $shift): void
    {
        $this->shifts[] = $shift;
    }

    /**
     * Returns the current shifts
     *
     * @return DeliveryShift[]
     */
    public function getShifts(): array
    {
        return $this->shifts;
    }

    /**
     * Sets the current shifts
     *
     * @param DeliveryShift[] $shifts
     */
    public function setShifts(array $shifts): void
    {
        $this->shifts = $shifts;
    }

    /**
     * Returns a shift at a specific index
     *
     * @param $index
     *
     * @return DeliveryShift
     */
    public function getShift($index): DeliveryShift
    {

        return $this->shifts[$index];

        throw new RuntimeException(sprintf('Shift %d cannt be found in system', $index));
    }
}
