<?php

namespace PicupTechnologies\WooPicupShipping\Notices;

/**
 * General purpose Picup error notice
 *
 * @package PicupTechnologies\WooPicupShipping\Notices
 */
final class PicupErrorNotice
{
    private $_message;

    public function __construct($message)
    {
        $this->_message = $message;

        add_action('admin_notices', [$this, 'render']);
    }

    public function render()
    {
        printf('<div class="error woocommerce-message"><p>%s</p></div>', $this->_message);
    }
}
