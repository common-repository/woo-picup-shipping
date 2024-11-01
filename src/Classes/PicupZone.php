<?php

namespace PicupTechnologies\WooPicupShipping\Classes;

/**
 * Holds a single zone -> warehouseId link used in
 * PicupZoneWarehouseSettings
 *
 * @package PicupTechnologies\WooPicupShipping\Classes
 */
final class PicupZone
{
    /**
     * @var string
     */
    private $warehouseId;

    /**
     * @var int
     */
    private $zoneId;

    /**
     * @return string
     */
    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    /**
     * @param string $warehouseId
     */
    public function setWarehouseId(string $warehouseId): void
    {
        $this->warehouseId = $warehouseId;
    }

    /**
     * @return int
     */
    public function getZoneId(): int
    {
        return $this->zoneId;
    }

    /**
     * @param int $zoneId
     */
    public function setZoneId(int $zoneId): void
    {
        $this->zoneId = $zoneId;
    }
}
