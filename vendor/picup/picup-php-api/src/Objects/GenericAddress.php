<?php

declare(strict_types=1);

namespace PicupTechnologies\PicupPHPApi\Objects;
use JsonSerializable;
/**
 * Class DeliveryReceiverAddress
 *
 * @package PicupTechnologies\PicupPHPApi\Objects
 */
class GenericAddress implements JsonSerializable
{
    /**
     * @var string
     */
    private $unitNo;

    /**
     * @var string
     */
    private $complex;

    /**
     * @var string
     */
    private $streetOrFarmNo;

    /**
     * @var string
     */
    private $streetOrFarm;

    /**
     * @var string
     */
    private $suburb;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string
     */
    private $country;

    /**
     * @var float
     */
    private $latitude;

    /**
     * @var float
     */
    private $longitude;

    /**
     * @return string
     */
    public function getUnitNo() : ?string
    {
        return $this->unitNo;
    }

    public function setUnitNo(string $unitNo) : void
    {
        $this->unitNo = $unitNo;
    }

    /**
     * @return string
     */
    public function getComplex() : ?string
    {
        return $this->complex;
    }

    public function setComplex(string $complex) : void
    {
        $this->complex = $complex;
    }

    /**
     * @return string
     */
    public function getStreetOrFarmNo() : ?string
    {
        return $this->streetOrFarmNo;
    }

    public function setStreetOrFarmNo(string $streetOrFarmNo) : void
    {
        $this->streetOrFarmNo = $streetOrFarmNo;
    }

    /**
     * @return string
     */
    public function getStreetOrFarm() : ?string
    {
        return $this->streetOrFarm;
    }

    public function setStreetOrFarm(string $streetOrFarm) : void
    {
        $this->streetOrFarm = $streetOrFarm;
    }

    /**
     * @return string
     */
    public function getSuburb() : ?string
    {
        return $this->suburb;
    }

    public function setSuburb(string $suburb) : void
    {
        $this->suburb = $suburb;
    }

    /**
     * @return string
     */
    public function getCity() : ?string
    {
        return $this->city;
    }

    public function setCity(string $city) : void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getPostalCode() : ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode) : void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getCountry() : ?string
    {
        return $this->country;
    }

    public function setCountry(string $country) : void
    {
        $this->country = $country;
    }

    /**
     * @return float
     */
    public function getLatitude() : ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude) : void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude() : ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude) : void
    {
        $this->longitude = $longitude;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @see  https://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {

        $address = [];
        if (!empty($this->unitNo))
        {
            $address[] = trim($this->unitNo);
        }

        if (!empty($this->streetOrFarmNo))
        {
            $address[] = trim($this->streetOrFarmNo);
        }

        if (!empty($this->complex))
        {
            $address[] = trim($this->complex);
        }

        if (!empty($this->suburb))
        {
            $address[] = trim($this->suburb);
        }

        if (!empty($this->city))
        {
            $address[] = trim($this->city);
        }

        if (!empty($this->postalCode))
        {
            $address[] = trim($this->postalCode);
        }

        if (!empty($this->country))
        {
            $address[] = trim($this->country);
        }

        $address = implode(", ", $address);

        return [
            'address' => $address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
