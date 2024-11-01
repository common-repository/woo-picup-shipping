<?php

namespace PicupTechnologies\WooPicupShipping\Classes;

use PicupTechnologies\PicupPHPApi\Exceptions\PicupApiException;
use PicupTechnologies\PicupPHPApi\Responses\DeliveryQuoteResponse;

/**
 * DTO to store the rate as expected by Woocommerce
 *
 * @package PicupTechnologies\WooPicupShipping\Classes
 */
final class WooCommerceRate
{
    private $id;
    private $label;
    private $cost;
    private $calcTax;

    /**
     * Builds a WooCommerce Rate from the DeliveryQuote Response
     *
     * We only use the first service type
     *
     * @param DeliveryQuoteResponse $deliveryQuoteResponse
     *
     * @return static
     * @throws PicupApiException
     */
    public static function make(DeliveryQuoteResponse $deliveryQuoteResponse): self
    {
        $rate = new self();

        if (!$deliveryQuoteResponse->isValid()) {
            throw new PicupApiException($deliveryQuoteResponse->getError());
        }
        $firstServiceType = $deliveryQuoteResponse->getServiceTypes()[0];

        $rate->setId('Picup OnDemand');
        $rate->setCost($firstServiceType->getPriceInclusive());
        $rate->setLabel('Picup On-Demand via ' . $firstServiceType->getVehicleName());
        $rate->setCalcTax('per_order');

        return $rate;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label): void
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param mixed $cost
     */
    public function setCost($cost): void
    {
        $this->cost = $cost;
    }

    /**
     * @return mixed
     */
    public function getCalcTax()
    {
        return $this->calcTax;
    }

    /**
     * @param mixed $calcTax
     */
    public function setCalcTax($calcTax): void
    {
        $this->calcTax = $calcTax;
    }

    /**
     * Returns the array representation of this DTO
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'label'    => $this->label,
            'cost'     => $this->cost,
            'calc_tax' => $this->calcTax,
            'taxes'    => '',           // blank means woo calculates it for you. false means no tax.
        ];
    }
}
