<?php

namespace PicupTechnologies\WooPicupShipping\Classes;

/**
 * Stores the association between Shipping Zones and Warehouses as set
 * by the shop owner in the plugin settings.
 *
 * @package PicupTechnologies\WooPicupShipping\Classes
 */
final class PicupZoneWarehouseSettings
{
    /**
     * @var PicupZone[]
     */
    private $zones;

    /**
     * @param array $data
     *
     * @return static
     */
    public static function make(array $data): self
    {
        $settings = new self;

        foreach ($data as $possibleZone => $possibleWarehouse) {
            // Key doesnt contain a valid zone
            if (strpos($possibleZone, 'zone-') !== 0) {
                continue;
            }

            $zoneId = substr($possibleZone, 5);

            // Value doesnt contain a valid warehouse
            if (strpos($possibleWarehouse, 'warehouse-') !== 0) {
                continue;
            }

            $zone = new PicupZone();
            $zone->setZoneId($zoneId);
            $zone->setWarehouseId($possibleWarehouse);
            $settings->addZone($zone);
        }

        return $settings;
    }

    /**
     * @param PicupZone $zone
     */
    public function addZone(PicupZone $zone): void
    {
        $this->zones[] = $zone;
    }

    /**
     * @return PicupZone[]
     */
    public function getZones(): array
    {
        return $this->zones;
    }

    /**
     * @param PicupZone[] $zones
     */
    public function setZones(array $zones): void
    {
        $this->zones = $zones;
    }

    public function hasZone(int $zoneId): bool
    {
        if (! $this->zones || !is_array($this->zones) || empty($this->zones)) {
            return false;
        }

        foreach ($this->zones as $zone) {
            if ($zone->getZoneId() === $zoneId) {
                return true;
            }
        }

        return false;
    }

    public function getZone(int $zoneId): ?PicupZone
    {
        if (! $this->hasZone($zoneId)) {
            return null;
        }

        foreach ($this->zones as $zone) {
            if ($zone->getZoneId() === $zoneId) {
                return $zone;
            }
        }

        return null;
    }
}
