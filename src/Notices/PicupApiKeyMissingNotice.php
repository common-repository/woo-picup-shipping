<?php
/**
 * Created by PhpStorm.
 * User: bryan
 * Date: 2019/02/11
 * Time: 12:29 PM
 */

namespace PicupTechnologies\WooPicupShipping\Notices;

/**
 * Missing API Key error message
 *
 * @package PicupTechnologies\WooPicupShipping\Notices
 */
final class PicupApiKeyMissingNotice
{
    private $_message = 'The Picup Plugin requires a valid api key to work';

    public function __construct($message = null)
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
