<?php

namespace PicupTechnologies\WooPicupShipping\Classes;

use InvalidArgumentException;

/**
 * Contains the options specific to Picup that we store in the
 * database and need for API access
 *
 * @package PicupTechnologies\WooPicupShipping\Classes
 */
final class PicupApiOptions
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $testApiKey;

    /**
     * @var bool
     */
    private $liveMode;

    /**
     * The currently selected Warehouse
     *
     * @var string
     */
    private $warehouse;

    /**
     * Default shipping contact person
     *
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $telephone;

    /**
     * @var string
     */
    private $cellphone;

    /**
     * @var string
     */
    private $specialInstructions;

    /**
     * @var string
     */
    private $onDemandPrepTime;


    /**
     * @var boolean
     */
    private $isConsolidateBoxes = false;


    /**
     * @var boolean
     */
    private $freeShippingEnabled = false;


    /**
     * @var boolean
     */
    private $outsideSouthAfricaEnabled = false;


    /**
     * @var float
     */
    private $freeShippingPriceThreshold = 1000;

    /**
     * @var int
     */
    private $consolidatedItemsPerBox = 1;

    private $consolidatedBoxSize = 'parcel-medium';


    /**
     * @var PicupZoneWarehouseSettings
     */
    private $warehouseSettings;

    /**
     * @var bool
     */
    private $thirdPartyCouriersEnabled = false;

    /**
     * @var bool
     */
    private $isUseContractDriversEnabled = false;


    /**
     * @var string Stores the date format that the site uses in their calendar.
     *             Required to parse the date correctly.
     *
     *             Currently set to F j, Y ie November 21, 2020
     */
    private $scheduledCustomDateFormat = 'F j, Y';

    /**
     * If user has selected Scheduled shipping, where must we
     * display the delivery shift options?
     *
     * 0 = Don't display (not recommended)
     * 1 = Display on cart
     * 2 = Display on checkout
     * 3 = Display on both
     *
     * @var int
     */
    private $scheduledDisplaySetting = 3;

    /**
     * If user has selected Scheduled shipping, where must we
     * display the delivery shift options?
     *
     * 0 = Processing
     * 1 = Completed
     *
     * @var int
     */
    private $deliveryCreationSetting = 1;



    public static function buildFromWordpress($data): self
    {
        $options = new self;

        if (isset($data['api_key'])) {
            $options->setApiKey($data['api_key']);
        }

        if (isset($data['test_api_key'])) {
            $options->setTestApiKey($data['test_api_key']);
        }

        if (isset($data['live_mode'])) {
            $options->setLiveMode($data['live_mode']);
        }

        if (isset($data['warehouse'])) {
            $options->setWarehouse($data['warehouse']);
        }

        if (isset($data['name'])) {
            $options->setName($data['name']);
        }

        if (isset($data['email'])) {
            $options->setEmail($data['email']);
        }

        if (isset($data['telephone'])) {
            $options->setTelephone($data['telephone']);
        }

        if (isset($data['cellphone'])) {
            $options->setCellphone($data['cellphone']);
        }

        if (isset($data['ondemand_prep_time'])) {
            $options->setOnDemandPrepTime($data['ondemand_prep_time']);
        }

        if (isset($data['is_consolidate_boxes'])) {
            $options->setIsConsolidateBoxes($data['is_consolidate_boxes']);
        }

        if (isset($data['consolidated_box_size'])) {
            $options->setConsolidatedBoxSize($data['consolidated_box_size']);
        }


        if (isset($data['consolidated_items_per_box'])) {
            $options->setConsolidatedItemsPerBox($data['consolidated_items_per_box']);
        }

        if (isset($data['special_instructions'])) {
            $options->setSpecialInstructions($data['special_instructions']);
        }

        if (isset($data['warehouse_zones']) && is_array($data['warehouse_zones'])) {
            $options->setWarehouseSettings(PicupZoneWarehouseSettings::make($data['warehouse_zones']));
        }

        if (isset($data['scheduled_display_setting'])) {
            $options->setScheduledDisplaySetting($data['scheduled_display_setting']);
        }

        if (isset($data['delivery_creation_setting'])) {
            $options->setDeliveryCreationSetting($data['delivery_creation_setting']);
        }

        if (isset($data['third_party_couriers'])) {
            $options->setThirdPartyCouriersEnabled($data['third_party_couriers']);
        }

        if (isset($data['contract_drivers'])) {
            $options->setIsUseContractDrivers($data['contract_drivers']);
        }

        if (isset($data['scheduled_custom_date_format'])) {
            $options->setScheduledCustomDateFormat($data['scheduled_custom_date_format']);
        }

        if (isset($data['free_shipping_enabled'])) {
            $options->setFreeShippingEnabled($data['free_shipping_enabled']);
        }

        if (isset($data['free_shipping_enabled'])) {
            $options->setFreeShippingPriceThreshold($data['free_shipping_price_threshold']);
        }

        if (isset($data['outside_south_africa_enabled'])) {
            $options->setOutsideSouthAfricaEnabled($data['outside_south_africa_enabled']);
        }

        return $options;
    }

    /**
     * @return string
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getTestApiKey(): ?string
    {
        return $this->testApiKey;
    }

    /**
     * @param string $testApiKey
     */
    public function setTestApiKey(string $testApiKey): void
    {
        $this->testApiKey = $testApiKey;
    }

    /**
     * @return bool
     */
    public function isLiveMode(): ?bool
    {
        return $this->liveMode;
    }

    /**
     * @param bool $liveMode
     */
    public function setLiveMode(bool $liveMode): void
    {
        $this->liveMode = $liveMode;
    }

    /**
     * @return string
     */
    public function getWarehouse(): ?string
    {
        return $this->warehouse;
    }

    /**
     * @param string $warehouse
     */
    public function setWarehouse(string $warehouse): void
    {
        $this->warehouse = $warehouse;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    /**
     * @param string $telephone
     */
    public function setTelephone(string $telephone): void
    {
        $this->telephone = $telephone;
    }

    /**
     * @return string
     */
    public function getCellphone(): ?string
    {
        return $this->cellphone;
    }

    /**
     * @param string $cellphone
     */
    public function setCellphone(string $cellphone): void
    {
        $this->cellphone = $cellphone;
    }

    /**
     * @return string
     */
    public function getSpecialInstructions(): ?string
    {
        return $this->specialInstructions;
    }

    /**
     * @param string $specialInstructions
     */
    public function setSpecialInstructions(string $specialInstructions): void
    {
        $this->specialInstructions = $specialInstructions;
    }

    /**
     * @return string
     */
    public function getOnDemandPrepTime(): ?string
    {
        return $this->onDemandPrepTime;
    }

    /**
     * @param string $onDemandPrepTime
     */
    public function setOnDemandPrepTime(string $onDemandPrepTime): void
    {
        $this->onDemandPrepTime = $onDemandPrepTime;
    }

    /**
     * @return bool
     */
    public function hasWarehouseSettings(): bool
    {
        return $this->warehouseSettings !== null;
    }

    /**
     * @return PicupZoneWarehouseSettings
     */
    public function getWarehouseSettings(): PicupZoneWarehouseSettings
    {
        return $this->warehouseSettings;
    }

    /**
     * @param PicupZoneWarehouseSettings $warehouseSettings
     */
    public function setWarehouseSettings(PicupZoneWarehouseSettings $warehouseSettings): void
    {
        $this->warehouseSettings = $warehouseSettings;
    }

    /**
     * @return int
     */
    public function getScheduledDisplaySetting(): int
    {
        return $this->scheduledDisplaySetting;
    }

    /**
     * @param int $scheduledDisplaySetting
     */
    public function setScheduledDisplaySetting(int $scheduledDisplaySetting): void
    {
        if ($scheduledDisplaySetting > 3) {
            throw new InvalidArgumentException('Invalid setting');
        }

        $this->scheduledDisplaySetting = $scheduledDisplaySetting;
    }

    /**
     * @return int
     */
    public function getDeliveryCreationSetting(): int
    {
        return $this->deliveryCreationSetting;
    }

    /**
     * @param int $deliveryCreationSetting
     */
    public function setDeliveryCreationSetting(int $deliveryCreationSetting): void
    {
        if ($deliveryCreationSetting > 1) {
            throw new InvalidArgumentException('Invalid setting');
        }

        $this->deliveryCreationSetting = $deliveryCreationSetting;
    }

    /**
     * @return bool
     */
    public function isThirdPartyCouriersEnabled(): bool
    {
        return $this->thirdPartyCouriersEnabled;
    }

    /**
     * @param bool $thirdPartyCouriersEnabled
     */
    public function setThirdPartyCouriersEnabled(bool $thirdPartyCouriersEnabled): void
    {
        $this->thirdPartyCouriersEnabled = $thirdPartyCouriersEnabled;
    }


    /**
     * @return bool
     */
    public function isUseContractDrivers(): bool
    {
        return $this->isUseContractDriversEnabled;
    }


    /**
     * @param bool $isUseContractDriversEnabled
     */
    public function setIsUseContractDrivers(bool $isUseContractDriversEnabled): void
    {
        $this->isUseContractDriversEnabled = $isUseContractDriversEnabled;
    }


    /**
     * @return string
     */
    public function getScheduledCustomDateFormat(): string
    {
        return $this->scheduledCustomDateFormat;
    }

    /**
     * @param string $scheduledCustomDateFormat
     */
    public function setScheduledCustomDateFormat(string $scheduledCustomDateFormat): void
    {
        $this->scheduledCustomDateFormat = $scheduledCustomDateFormat;
    }


    /**
     * @param bool $value
     */
    public function setFreeShippingEnabled(bool $value): void
    {
        $this->freeShippingEnabled = $value;
    }

    /**
     * @return bool
     */
    public function getOutsideSouthAfricaEnabled(): bool
    {
        return $this->outsideSouthAfricaEnabled;
    }

    /**
     * @param bool $value
     */
    public function setOutsideSouthAfricaEnabled(bool $value): void
    {
        $this->outsideSouthAfricaEnabled = $value;
    }

    /**
     * @return bool
     */
    public function getFreeShippingEnabled(): bool
    {
        return $this->freeShippingEnabled;
    }

    /**
     * @param float $value
     */
    public function setFreeShippingPriceThreshold(float $value): void
    {
        $this->freeShippingPriceThreshold = $value;
    }

    public function getFreeShippingPriceThreshold(): float
    {
        return $this->freeShippingPriceThreshold;
    }

    /**
     * @param bool $isConsolidateBoxes
     */
    public function setIsConsolidateBoxes(bool $isConsolidateBoxes): void
    {
        $this->isConsolidateBoxes = $isConsolidateBoxes;
    }

    /**
     * @return bool
     */
    public function getIsConsolidateBoxes(): bool
    {
        return $this->isConsolidateBoxes;
    }

    /**
     * @return string
     */
    public function getConsolidatedBoxSize(): string
    {
        return $this->consolidatedBoxSize;
    }

    /**
     * @param string $consolidatedBoxSize
     */
    public function setConsolidatedBoxSize(string $consolidatedBoxSize): void
    {
        $this->consolidatedBoxSize = $consolidatedBoxSize;
    }

    /**
     * @return int
     */
    public function getConsolidatedItemsPerBox(): int
    {
        return $this->consolidatedItemsPerBox;
    }

    /**
     * @param int $consolidatedItemsPerBox
     */
    public function setConsolidatedItemsPerBox(int $consolidatedItemsPerBox): void
    {
        $this->consolidatedItemsPerBox = $consolidatedItemsPerBox;
    }




    /**
     * Returns an array representation of this object
     * as expected by wordpress
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'api_key'              => $this->apiKey,
            'warehouse'            => $this->warehouse,
            'scheduled_display_setting' => $this->scheduledDisplaySetting,
            'delivery_creation_setting' => $this->deliveryCreationSetting,
            'name'                 => $this->name,
            'email'                => $this->email,
            'telephone'            => $this->telephone,
            'cellphone'            => $this->cellphone,
            'special_instructions' => $this->specialInstructions,
            'ondemand_prep_time' => $this->onDemandPrepTime,
            'contract_drivers' => $this->isUseContractDriversEnabled,
            'is_consolidate_boxes' => $this->isConsolidateBoxes,
            'consolidated_box_size' => $this->consolidatedBoxSize,
            'consolidated_items_per_box' => $this->consolidatedItemsPerBox,
            'scheduled_custom_date_format' => $this->scheduledCustomDateFormat,
            'free_shipping_enabled' => $this->freeShippingEnabled,
            'free_shipping_price_threshold' => $this->freeShippingPriceThreshold,
            'outside_south_africa_enabled' => $this->outsideSouthAfricaEnabled
        ];
    }
}
