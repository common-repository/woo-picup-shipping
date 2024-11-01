<?php

/** @var \PicupTechnologies\PicupPHPApi\Responses\OrderStatusResponse $orderStatusResponse */
/** @var int $picupId */
?>

<!-- <p>Picup ID:</p>
<table>
    <thead></thead>
    <tbody>
        <tr>
            <td>Request ID</td>
            <td><?php echo esc_html($picupId); ?></td>
        </tr>

    </tbody>
</table>
 -->

<?php foreach ($orderStatusResponse->getOrderStatuses() as $orderStatus) { ?>

    <?php foreach ($orderStatus->getParcelStatuses() as $parcelStatus) { ?>
        <h3 style="margin-bottom:5px;">
            <td><?php echo esc_html($parcelStatus->getReferenceNumber()); ?></td>
        </h3>
        <p style="margin:0px;">Status: <br>
            <strong><?php echo esc_html($parcelStatus->getStatus()); ?></strong></p>
        <p style="margin-top:5px;">
            Last Updated: <br>
            <strong><?php
                    $date = new DateTime($parcelStatus->getTimestamp());
                    echo $date->format('Y-m-d H:i:s');
                    ?></strong>
        </p>

    <?php } ?>
    <h3></h3>
    <hr style="border-bottom:1px solid #fff;">
<?php } ?>