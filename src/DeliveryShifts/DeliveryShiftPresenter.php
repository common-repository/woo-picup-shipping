<?php

namespace PicupTechnologies\WooPicupShipping\DeliveryShifts;

/**
 * Class DeliveryShiftPresenter
 *
 * Responsible for taking a DeliveryShiftCollection and presenting a list
 * of shifts with their dates.
 */
final class DeliveryShiftPresenter
{
    /**
     * @var DeliveryShiftCollection
     */
    private $deliveryShifts;

    /**
     * DeliveryShiftPresenter constructor.
     *
     * @param DeliveryShiftCollection $deliveryShifts
     */
    public function __construct(DeliveryShiftCollection $deliveryShifts)
    {
        $this->deliveryShifts = $deliveryShifts;
    }

    /**
     * Present the shifts in a readable format for display
     *
     * @return string[]
     */
    public function presentShifts(): array
    {
        $return = [];

        /**
         * @var int           $shiftKey
         * @var DeliveryShift $deliveryShift
         */
        foreach ($this->deliveryShifts->getShifts() as $deliveryShift) {
            $shiftStartDateTime = $deliveryShift->getShiftStartDate();
            $shiftEndDateTime = $deliveryShift->getShiftEndDate();

            $timeString = sprintf('%s to %s',
                $shiftStartDateTime->format('H:i'),
                $shiftEndDateTime->format('H:i')
            );
            $dateString = $timeString . ' ' . $shiftStartDateTime->format('\o\n D jS M');
            $return[$deliveryShift->getId()] = $dateString;
        }

        return $return;
    }
}
