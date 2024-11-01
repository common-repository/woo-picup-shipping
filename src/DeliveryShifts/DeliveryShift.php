<?php
/**
 * Created by PhpStorm.
 * User: bryan
 * Date: 2019/02/14
 * Time: 10:36 AM
 */

namespace PicupTechnologies\WooPicupShipping\DeliveryShifts;

use DateInterval;
use DateTime;
use Exception;

final class DeliveryShift
{
    /**
     * @var int Original id of the shift as stored in the wp_options table.
     *          Required so that we can point back to the exact shift again later.
     */
    private $id;

    /**
     * @var DateTime
     */
    private $shiftStartDate;

    /**
     * @var DateTime
     */
    private $shiftEndDate;

    /**
     * DeliveryShift constructor.
     *
     * @param int      $id        Original id (array key) from shifts in wp_options
     * @param int      $dayOfWeek ISO-8601 Day of Week (1 = Monday, 7 = Sunday) DateFormat field name is "N"
     * @param DateTime $shiftStart
     * @param DateTime $shiftEnd
     *
     * @param DateTime $referenceDateTime
     *
     * @throws Exception
     */
    public function __construct($id, $dayOfWeek, DateTime $shiftStart, DateTime $shiftEnd, DateTime $referenceDateTime)
    {
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        $this->id = $id;

        // build date time
        $shiftStartDateString = sprintf('%s %s', $days[$dayOfWeek], $shiftStart->format('H:i'));
        $this->shiftStartDate = new \DateTime($shiftStartDateString);

        $shiftStartDateString = sprintf('%s %s', $days[$dayOfWeek], $shiftEnd->format('H:i'));
        $this->shiftEndDate = new \DateTime($shiftStartDateString);

        // if start date is today then add 1 week to both dates
        $oneWeekPeriod = new \DateInterval('P1W');
        if ($this->shiftStartDate->format('w') === $referenceDateTime->format('w')) {
            $this->addToBothDates($oneWeekPeriod);
        }
    }

    /**
     * Add a DateInterval to both Shift Start and Shift End dates
     *
     * @param DateInterval $interval
     */
    public function addToBothDates(DateInterval $interval): void
    {
        $this->shiftStartDate->add($interval);
        $this->shiftEndDate->add($interval);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getShiftStartDate(): DateTime
    {
        return $this->shiftStartDate;
    }

    /**
     * @param DateTime $shiftStartDate
     */
    public function setShiftStartDate(DateTime $shiftStartDate): void
    {
        $this->shiftStartDate = $shiftStartDate;
    }

    /**
     * @return DateTime
     */
    public function getShiftEndDate(): DateTime
    {
        return $this->shiftEndDate;
    }

    /**
     * @param DateTime $shiftEndDate
     */
    public function setShiftEndDate(DateTime $shiftEndDate): void
    {
        $this->shiftEndDate = $shiftEndDate;
    }
}
