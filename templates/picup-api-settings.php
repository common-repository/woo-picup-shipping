<?php

/** @var \PicupTechnologies\PicupPHPApi\Responses\DeliveryIntegrationDetailsResponse $integrationDetails */
/** @var \PicupTechnologies\WooPicupShipping\Classes\PicupApiOptions $picupApiOptions */
/** @var array $zones */

$picupScheduledDisplayOptions = [
    0 => 'Dont display',
    1 => 'Display in cart',
    2 => 'Display in checkout',
    3 => 'Display in cart and checkout'
];

// TODO: pull this in from api
$picupParcelSizes = [
    0 => 'parcel-envelope',
    1 => 'parcel-small',
    2 => 'parcel-medium',
    3 => 'parcel-large',
    4 => 'parcel-xlarge',
];

$picupProcessingDisplayOptions = [
    0 => 'Processing',
    1 => 'Completed'
];

?>
<div class="wrap picup">

    <img src="https://dashboard.picup.co.za/assets/img/picup-logo.svg" alt="">
    <h1>Global Plugin Settings</h1>
    <form method="post" action="">
        <?php settings_fields('picup-api-options-group'); ?>
        <?php do_settings_sections('picup_plugin_settings'); ?>

        <div class="columns">
            <div class="main-settings">
                <h1>Main Settings</h1>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="picup-api-options-api-key">Picup Live API Key:</label></th>
                        <td>
                            <input type="text" id="picup-api-options-api-key" name="picup-api-options[api_key]" value="<?php echo esc_attr($picupApiOptions->getApiKey()); ?>" />
                            <br>* Required. Add the live API key provided by Picup.
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="picup-api-options-test-api-key">Picup Testing API Key:</label></th>
                        <td>
                            <input type="text" id="picup-api-options-test-api-key" name="picup-api-options[test_api_key]" value="<?php echo esc_attr($picupApiOptions->getTestApiKey()); ?>" />
                            <br>* Required. Add the testing API key provided by Picup.
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="picup-api-options-live-mode">Live Mode:</label></th>
                        <td>
                            <?php
                            $liveModeChecked = '';
                            if ($picupApiOptions->isLiveMode()) {
                                $liveModeChecked = ' checked ';
                            }
                            ?>
                            <input type="hidden" name="picup-api-options[live_mode]" value="0" />
                            <input type="checkbox" id="picup-api-options-live-mode" name="picup-api-options[live_mode]" value="1" <?= $liveModeChecked ?> /> During live mode deliveries will be dispatched
                        </td>
                    </tr>
                    <tr>
                        <th>Enable 3rd Party Couriers:</th>
                        <td>
                            <?php
                            $couriersChecked = '';
                            if ($picupApiOptions->isThirdPartyCouriersEnabled()) {
                                $couriersChecked = ' checked ';
                            }
                            ?>
                            <input type="hidden" name="picup-api-options[third_party_couriers]" value="0" />
                            <input type="checkbox" id="picup-api-options-third-party-couriers" name="picup-api-options[third_party_couriers]" value="1" <?= $couriersChecked ?> />  Post Paid Accounts Only

                        </td>
                    </tr>
                    <tr>
                        <th>Picup TMS:</th>
                        <td>
                            <?php
                            $contractDrivesChecked = '';
                            if ($picupApiOptions->isUseContractDrivers()) {
                                $contractDrivesChecked = ' checked ';
                            }
                            ?>
                            <input type="hidden" name="picup-api-options[contract_drivers]" value="0" />
                            <input type="checkbox" id="picup-api-options-contract-drivers" name="picup-api-options[contract_drivers]" value="1" <?= $contractDrivesChecked ?> /> Use Contract Drivers

                        </td>
                    </tr>


                </table>
                <h3>Collection Contact Details:</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="picup-api-name">Name:</label></th>
                        <td><input type="text" name="picup-api-options[name]" value="<?php echo esc_attr($picupApiOptions->getName()); ?>" id="picup-api-name" /></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="picup-api-email">Email:</label></th>
                        <td>
                            <input type="text" name="picup-api-options[email]" value="<?php echo esc_attr($picupApiOptions->getEmail()); ?>" id="picup-api-email" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="picup-api-cellphone">Cellphone:</label></th>
                        <td><input type="text" name="picup-api-options[cellphone]" value="<?php echo esc_attr($picupApiOptions->getCellphone()); ?>" id="picup-api-cellphone" /></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="picup-api-options-special-instructions">Special Instructions:</label></th>
                        <td>
                            <textarea rows="3" id="picup-api-options-special-instructions" name="picup-api-options[special_instructions]"><?php echo esc_textarea($picupApiOptions->getSpecialInstructions()); ?></textarea>
                            <br>Any special instructions relating to shipments from this store
                        </td>
                    </tr>
                </table>
                <h3>Display Details:</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="picup-api-scheduled-display-setting">Delivery Shift Display:</label></th>
                        <td>
                            <select id="picup-api-scheduled-display-setting" name="picup-api-options[scheduled_display_setting]">
                                <?php foreach ($picupScheduledDisplayOptions as $displayId => $displayInfo) { ?>
                                    <?php
                                    $selected = '';
                                    if ($displayId === $picupApiOptions->getScheduledDisplaySetting()) {
                                        $selected = ' selected ';
                                    }
                                    ?>
                                    <option value="<?= $displayId ?>" <?= $selected ?>><?= $displayInfo ?></option>
                                <?php  } ?>
                            </select>
                            <br>Set where you would like the delivery shift dropdown to appear if you use Scheduled Delivery
                        </td>
                    </tr>


                    <tr>
                        <th scope="row"><label for="picup-api-scheduled-custom-date-format">ScheduledCustom Date Format:</label></th>
                        <td>
                            <input type="text" name="picup-api-options[scheduled_custom_date_format]" value="<?php echo esc_attr($picupApiOptions->getScheduledCustomDateFormat()); ?>" id="picup-api-scheduled-custom-date-format" />
                        </td>
                    </tr>

                </table>
                <h3>Delivery Creation Event:</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="picup-api-delivery-creation-setting">Delivery Creation Event:</label></th>
                        <td>
                            <select id="picup-api-delivery-creation-setting" name="picup-api-options[delivery_creation_setting]">
                                <?php foreach ($picupProcessingDisplayOptions as $displayId => $displayInfo) { ?>
                                    <?php
                                    $selected = '';
                                    if ($displayId === $picupApiOptions->getDeliveryCreationSetting()) {
                                        $selected = ' selected ';
                                    }
                                    ?>
                                    <option value="<?= $displayId ?>" <?= $selected ?>><?= $displayInfo ?></option>
                                <?php  } ?>
                            </select>
                            <br>Set when you want your Picup delivery option to be created during the order process
                        </td>
                    </tr>
                </table>
            </div>

            <div class="warehouse-settings">
                <h3>Warehouse Zones</h3>
                <p>Here you can set a specific warehouse for all of your shipping zones you have defined.</p>

                <?php if (!$integrationDetails) { ?>
                    <p><strong>Cannot display warehouses until a valid API key has been set.</strong></p>
                <?php } else { ?>
                    <?php foreach ($zones as $zoneId => $zone) { ?>
                        <h4><label for="picup-api-zone-<?= $zoneId ?>"><?= $zone['zone_name'] ?></label></h4>
                        <select id="picup-api-zone-<?= $zoneId ?>" name="picup-api-options[warehouse_zones][zone-<?= $zoneId ?>]warehouse_id">
                            <option value="">Select...</option>
                            <?php foreach ($integrationDetails->getWarehouses() as $warehouse) { ?>
                                <?php
                                $selected = '';
                                if (
                                    $picupApiOptions->hasWarehouseSettings() &&
                                    $picupApiOptions->getWarehouseSettings()->hasZone($zoneId) &&
                                    $picupApiOptions->getWarehouseSettings()->getZone($zoneId)->getWarehouseId() === $warehouse->getId()
                                ) {
                                    $selected = ' selected ';
                                }
                                ?>
                                <option value="<?php echo esc_html($warehouse->getId()); ?>" <?= $selected ?>><?= esc_html($warehouse->getName()) ?></option>
                            <?php  } ?>
                        </select>
                    <?php } ?>
                <?php } ?>

                <br> <br>

                
                <h3>On Demand Prep Time</h3>
                <p>The amount of time added to the order to allow for warehouse operations. Please note on average 15mins are allocated for driver selection.</p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="picup-api-on-demand-prep-time">On Demand Prep Time (minutes):</label></th>
                        <td>
                            <input type="text" name="picup-api-options[ondemand_prep_time]" value="<?php echo esc_attr($picupApiOptions->getOnDemandPrepTime()); ?>" id="picup-api-on-demand-prep-time" />
                            <br>
                        </td>
                    </tr>

                </table>

                <br>

                <h3>Consolidate Order Items</h3>
                <p>By default the Picup API will make each order as a single parcel. Enable consolidation if you would like to group items into a single box.
                    <br>Please note you will be billed for incorrect parcel sizes on collection
                </p>
                <?php
                $consolidatedBoxesChecked = false;
                if ($picupApiOptions->getIsConsolidateBoxes()) {
                    $consolidatedBoxesChecked = ' checked ';
                }
                ?>
                <table class="form-table">
                    <tr>
                        <th>
                            Enable Order Consolidation
                        </th>
                        <td>
                            <input type="hidden" name="picup-api-options[is_consolidate_boxes]" value="0" />
                            <input type="checkbox" id="picup-api-options-is-consolidated-boxes" name="picup-api-options[is_consolidate_boxes]" value="1" <?= $consolidatedBoxesChecked ?> />

                        </td>

                    </tr>
                    <tr>
                        <th scope="row"><label for="picup-api-consolidated_box_size">Consolidated Parcel Size:</label></th>
                        <td>
                            <select id="picup-api-consolidated_box_size" name="picup-api-options[consolidated_box_size]">
                                <?php foreach ($picupParcelSizes as $displayId => $displayInfo) { ?>
                                    <?php
                                    $selected = '';
                                    if ($displayInfo === $picupApiOptions->getConsolidatedBoxSize()) {
                                        $selected = ' selected ';
                                    }
                                    ?>
                                    <option value="<?= $displayInfo ?>" <?= $selected ?>><?= $displayInfo ?></option>
                                <?php  } ?>
                            </select>

                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="picup-api-consolidated-items-per-box">Max Order Items Per Parcel</label></th>
                        <td>
                            <input type="text" name="picup-api-options[consolidated_items_per_box]" value="<?php echo esc_attr($picupApiOptions->getConsolidatedItemsPerBox()); ?>" id="picup-api--consolidated-items-per-box" />
                        </td>
                    </tr>
                </table>
                <?php
                $freeShippingEnabled = false;
                if ($picupApiOptions->getFreeShippingEnabled()) {
                    $freeShippingEnabled = ' checked ';
                }
                ?>
                <h3>Free Shipping</h3>
                <p>Free Shipping Enabled means that orders above a certain price threshold
                </p>
                <table class="form-table">
                    <tr>
                        <th>
                            Enable Free Shipping
                        </th>
                        <td>
                            <input type="hidden" name="picup-api-options[free_shipping_enabled]" value="0" />
                            <input type="checkbox" id="picup-api-options-free-shipping-enabled" name="picup-api-options[free_shipping_enabled]" value="1" <?= $freeShippingEnabled ?> />

                        </td>

                    </tr>
                    <tr>
                        <th scope="row"><label for="picup-api-free_shipping-price-threshold">Free Shipping Price Threshold</label></th>
                        <td>
                            <input type="text" name="picup-api-options[free_shipping_price_threshold]" value="<?php echo esc_attr($picupApiOptions->getFreeShippingPriceThreshold()); ?>" id="picup-api-free-shipping-price-threshold" />
                        </td>
                    </tr>
                </table>
                <?php
                $outsideSouthAfricaEnabled = false;
                if ($picupApiOptions->getOutsideSouthAfricaEnabled()) {
                    $outsideSouthAfricaEnabled = ' checked ';
                }
                ?>
                <h3>Outside South Africa</h3>
                <p>Click this option on if you login to the Picup domain ending in .africa
                </p>
                <table class="form-table">
                    <tr>
                        <th>
                            Outside of South Africa
                        </th>
                        <td>
                            <input type="hidden" name="picup-api-options[outside_south_africa_enabled]" value="0" />
                            <input type="checkbox" id="picup-api-options-outside-south-africa-enabled" name="picup-api-options[outside_south_africa_enabled]" value="1" <?= $outsideSouthAfricaEnabled ?> />
                        </td>

                    </tr>
                </table>
                <br><br><br>


            </div>
        </div>
        <?php submit_button(); ?>
    </form>
</div>

<style>
    img {
        padding-top:15px;
        max-width: 100px;
    }

    h1 {
        padding-bottom: 15px !important;
    }

    .picup input[type="text"],
    textarea,
    select {
        width: 90%;
    }

    .main-settings {
        grid-area: main;
    }

    .warehouse-settings {
        grid-area: warehouses;
        margin-top: 35px;
    }

    .picup .columns {
        display: grid;
        grid-gap: 10px;
        grid-template-areas:
            'main warehouses';
    }

    @media (max-width: 800px) {
        .picup .columns {
            grid-template-areas:
                'main'
                'warehouses';
        }
    }
</style>