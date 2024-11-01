<?php

namespace PicupTechnologies\WooPicupShipping\Notices;

/**
 * API is in testing mode notice
 *
 * @package PicupTechnologies\WooPicupShipping\Notices
 */
final class PicupApiTestingMode
{
    private $_message = 'The Picup Plugin is currently running in Test mode. Enable Live mode in Settings to dispatch actual deliveries.';

    public function __construct($message = '')
    {
        if ($message) {
            $this->_message = $message;
        }

        add_action('admin_notices', [$this, 'render']);
    }

    public function render(): void
    {
        printf('<div class="error woocommerce-message"><p>%s</p></div>', $this->_message);
    }
}
