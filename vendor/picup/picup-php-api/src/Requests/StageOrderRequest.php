<?php


namespace PicupTechnologies\PicupPHPApi\Requests;

use JsonSerializable;
use PicupTechnologies\PicupPHPApi\Contracts\PicupRequestInterface;
use PicupTechnologies\PicupPHPApi\Objects\DeliveryBucket\DeliveryShipment;



class StageOrderRequest  implements PicupRequestInterface, JsonSerializable
{
    private $warehouseId;
    private $shipments;

    /**
     * Sets the warehouse id
     * @param $warehouseId
     */
    public function setWarehouseId($warehouseId) {
        $this->warehouseId = $warehouseId;
    }

    /**
     * Gets the warehouse id
     * @return string
     */
    public function getWarehouseId() : string {
        return $this->warehouseId;
    }

    /**
     * @return DeliveryShipment[]
     */
    public function getShipments() : array
    {
        return $this->shipments;
    }

    /**
     * @param DeliveryShipment[] $shipments
     */
    public function setShipments(array $shipments) : void
    {
        $this->shipments = $shipments;
    }

    public function addShipment(DeliveryShipment $shipment) : void
    {
        $this->shipments[] = $shipment;
    }

    public function jsonSerialize()
    {
        return [
            'warehouse_id' => $this->warehouseId,
            'shipments' => $this->shipments
        ];
    }
}