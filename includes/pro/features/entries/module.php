<?php

/**
 * Entries Module – load CPT, save, API. Entries UI is only under CF7 Mate dashboard (#/entries).
 *
 * @package CF7_Mate\Features\Entries
 * @since 3.0.0
 */

namespace CF7_Mate\Features\Entries;

use CF7_Mate\Premium_Loader;
use CF7_Mate\Pro\Pro_Feature_Base;
use CF7_Mate\Pro\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

class Entries extends Pro_Feature_Base
{
    use Singleton;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function init()
    {
        $this->load_components();
    }

    private function load_components()
    {
        $dir = __DIR__ . '/';
        if (file_exists($dir . 'cpt.php')) {
            require_once $dir . 'cpt.php';
            new Entries_CPT();
        }
        if (file_exists($dir . 'save.php')) {
            require_once $dir . 'save.php';
            new Entries_Save();
        }
        if (is_admin() && file_exists($dir . 'editor-panel.php')) {
            require_once $dir . 'editor-panel.php';
            new Entries_Editor_Panel();
        }
        if (file_exists($dir . 'api.php')) {
            require_once $dir . 'api.php';
            new Entries_API();
        }
    }

    /**
     * Entries are only shown under CF7 Mate dashboard (#/entries). No submenu under Contact (wpcf7).
     */
    public function add_entries_menu()
    {
        // Intentionally empty: do not add "Entries" under Contact Form 7 menu.
    }
}
