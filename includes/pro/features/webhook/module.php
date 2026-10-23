<?php
/**
 * Webhook Module – send form submissions to external URLs (Pro).
 *
 * @package CF7_Mate\Features\Webhook
 * @since 3.0.0
 */

namespace CF7_Mate\Features\Webhook;

use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Webhook extends Pro_Feature_Base
{
    use Singleton;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $sender = __DIR__ . '/sender.php';
        if (file_exists($sender)) {
            require_once $sender;
            new Webhook_Sender();
        }
    }
}
